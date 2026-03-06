/**
 * 리뷰 위젯 JavaScript
 * 경로: includes/js/review_widget.js
 *
 * 의존성: 없음 (Vanilla JS)
 * API: /api/reviews.php (list, summary, create, like)
 */
(function() {
    'use strict';

    var ReviewWidget = {
        productType: '',
        currentPage: 1,
        currentSort: 'newest',
        perPage: 10,
        apiBase: '/api/reviews.php',
        selectedRating: 5,
        pendingPhotos: [],       // File objects for upload
        lightboxPhotos: [],      // current lightbox photo list [{src, alt}]
        lightboxIndex: 0,

        // ─── 초기화 ───
        init: function() {
            this.productType = window.__reviewProductType || '';
            if (!this.productType) return;

            this.bindEvents();
            this.loadSummary();
            this.loadReviews(1, 'newest');
        },

        // ─── 이벤트 바인딩 ───
        bindEvents: function() {
            var self = this;

            // 정렬 변경
            var sortSelect = document.getElementById('reviewSortSelect');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    self.currentSort = this.value;
                    self.loadReviews(1, self.currentSort);
                });
            }

            // 리뷰 쓰기 토글
            var writeToggle = document.getElementById('reviewWriteToggle');
            if (writeToggle) {
                writeToggle.addEventListener('click', function() {
                    self.toggleWriteForm();
                });
            }

            // 취소 버튼
            var cancelBtn = document.getElementById('reviewCancelBtn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    self.hideWriteForm();
                });
            }

            // 폼 제출
            var form = document.getElementById('reviewForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    self.submitReview();
                });
            }

            // 별점 선택
            var starSelector = document.getElementById('reviewStarSelector');
            if (starSelector) {
                var starBtns = starSelector.querySelectorAll('.review-star-btn');
                for (var i = 0; i < starBtns.length; i++) {
                    (function(btn) {
                        btn.addEventListener('mouseenter', function() {
                            var r = parseInt(this.getAttribute('data-rating'));
                            self.previewStars(r);
                        });
                        btn.addEventListener('click', function() {
                            var r = parseInt(this.getAttribute('data-rating'));
                            self.setRating(r);
                        });
                    })(starBtns[i]);
                }
                starSelector.addEventListener('mouseleave', function() {
                    self.previewStars(self.selectedRating);
                });
            }

            // 글자 수 카운트
            var contentArea = document.getElementById('reviewContent');
            if (contentArea) {
                contentArea.addEventListener('input', function() {
                    var countEl = document.getElementById('reviewCharCount');
                    if (countEl) countEl.textContent = this.value.length;
                });
            }

            // 사진 업로드
            var photoInput = document.getElementById('reviewPhotoInput');
            if (photoInput) {
                photoInput.addEventListener('change', function() {
                    self.handlePhotoUpload(this);
                });
            }

            // 라이트박스 닫기
            var lightbox = document.getElementById('reviewLightbox');
            if (lightbox) {
                var backdrop = lightbox.querySelector('.review-lightbox-backdrop');
                var closeBtn = lightbox.querySelector('.review-lightbox-close');
                var prevBtn  = lightbox.querySelector('.review-lightbox-prev');
                var nextBtn  = lightbox.querySelector('.review-lightbox-next');

                if (backdrop) backdrop.addEventListener('click', function() { self.closeLightbox(); });
                if (closeBtn) closeBtn.addEventListener('click', function() { self.closeLightbox(); });
                if (prevBtn)  prevBtn.addEventListener('click', function()  { self.lightboxPrev(); });
                if (nextBtn)  nextBtn.addEventListener('click', function()  { self.lightboxNext(); });
            }

            // ESC 키로 라이트박스 닫기
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    self.closeLightbox();
                }
                if (e.key === 'ArrowLeft') {
                    self.lightboxPrev();
                }
                if (e.key === 'ArrowRight') {
                    self.lightboxNext();
                }
            });
        },

        // ─── 별점 요약 로드 ───
        loadSummary: function() {
            var self = this;
            var url = this.apiBase + '?action=summary&product_type=' + encodeURIComponent(this.productType);

            fetch(url)
                .then(function(res) { return res.json(); })
                .then(function(json) {
                    if (json.success) {
                        self.renderSummary(json.data);
                    } else {
                        self.renderSummaryEmpty();
                    }
                })
                .catch(function() {
                    self.renderSummaryEmpty();
                });
        },

        renderSummary: function(data) {
            var container = document.getElementById('reviewSummary');
            if (!container) return;

            var avg = parseFloat(data.avg_rating) || 0;
            var total = parseInt(data.total_reviews) || 0;
            var dist = data.rating_distribution || {};

            if (total === 0) {
                this.renderSummaryEmpty();
                return;
            }

            var starsHtml = this.renderStarsHtml(avg);
            var distHtml = '';
            for (var r = 5; r >= 1; r--) {
                var cnt = parseInt(dist[r]) || 0;
                var pct = total > 0 ? Math.round((cnt / total) * 100) : 0;
                distHtml += '<div class="review-dist-row">' +
                    '<span class="review-dist-label">' + r + '★</span>' +
                    '<div class="review-dist-bar"><div class="review-dist-fill" style="width:' + pct + '%"></div></div>' +
                    '<span class="review-dist-count">' + cnt + '</span>' +
                '</div>';
            }

            container.innerHTML =
                '<div class="review-summary-content">' +
                    '<div class="review-summary-left">' +
                        '<span class="review-avg-score">' + avg.toFixed(1) + '</span>' +
                        '<div class="review-summary-stars">' + starsHtml + '</div>' +
                        '<span class="review-count">' + total + '건의 리뷰</span>' +
                    '</div>' +
                    '<div class="review-summary-right">' + distHtml + '</div>' +
                '</div>';

            // 툴바 표시
            var toolbar = document.getElementById('reviewToolbar');
            if (toolbar) toolbar.style.display = 'flex';
        },

        renderSummaryEmpty: function() {
            var container = document.getElementById('reviewSummary');
            if (!container) return;

            container.innerHTML =
                '<div class="review-summary-content">' +
                    '<div class="review-summary-left">' +
                        '<span class="review-avg-score">0.0</span>' +
                        '<div class="review-summary-stars">' + this.renderStarsHtml(0) + '</div>' +
                        '<span class="review-count">아직 리뷰가 없습니다</span>' +
                    '</div>' +
                '</div>';

            // 툴바는 리뷰 없어도 쓰기 버튼 위해 표시
            var toolbar = document.getElementById('reviewToolbar');
            if (toolbar) toolbar.style.display = 'flex';
        },

        // ─── 리뷰 목록 로드 ───
        loadReviews: function(page, sort) {
            var self = this;
            this.currentPage = page || 1;
            this.currentSort = sort || this.currentSort;

            var listEl = document.getElementById('reviewList');
            if (listEl) {
                listEl.innerHTML = '<div class="review-list-loading"><span class="review-loading-spinner"></span> 리뷰를 불러오는 중...</div>';
            }

            var url = this.apiBase +
                '?action=list' +
                '&product_type=' + encodeURIComponent(this.productType) +
                '&page=' + this.currentPage +
                '&per_page=' + this.perPage +
                '&sort=' + encodeURIComponent(this.currentSort);

            fetch(url)
                .then(function(res) { return res.json(); })
                .then(function(json) {
                    if (json.success) {
                        self.renderReviews(json.data);
                    } else {
                        self.renderReviewsEmpty();
                    }
                })
                .catch(function() {
                    self.renderReviewsEmpty();
                });
        },

        renderReviews: function(data) {
            var listEl = document.getElementById('reviewList');
            if (!listEl) return;

            var reviews = data.reviews || [];
            var total = data.total || 0;
            var page = data.page || 1;
            var perPage = data.per_page || 10;

            if (reviews.length === 0) {
                this.renderReviewsEmpty();
                return;
            }

            var html = '';
            for (var i = 0; i < reviews.length; i++) {
                html += this.renderReview(reviews[i]);
            }
            listEl.innerHTML = html;

            // 이벤트 바인딩 (좋아요, 사진 클릭)
            this.bindReviewEvents(listEl);

            // 페이지네이션
            this.renderPagination(total, page, perPage);
        },

        renderReviewsEmpty: function() {
            var listEl = document.getElementById('reviewList');
            if (!listEl) return;

            listEl.innerHTML =
                '<div class="review-list-empty">' +
                    '<div class="review-list-empty-icon">📝</div>' +
                    '<div class="review-list-empty-text">아직 리뷰가 없습니다</div>' +
                    '<div class="review-list-empty-sub">첫 번째 리뷰를 작성해보세요!</div>' +
                '</div>';

            var paginationEl = document.getElementById('reviewPagination');
            if (paginationEl) paginationEl.innerHTML = '';
        },

        renderReview: function(review) {
            var starsHtml = this.renderStarsHtml(review.rating || 5);
            var maskedName = this.maskName(review.user_name || '');
            var timeAgoStr = this.timeAgo(review.created_at || '');
            var title = review.title ? '<div class="review-card-title">' + this.escapeHtml(review.title) + '</div>' : '';
            var content = '<div class="review-card-content">' + this.escapeHtml(review.content || '') + '</div>';

            // 구매인증 배지
            var verifiedHtml = '';
            if (review.is_verified_purchase) {
                verifiedHtml = '<span class="review-verified-badge">✓ 구매인증</span>';
            }

            // 사진
            var photosHtml = '';
            if (review.photos && review.photos.length > 0) {
                photosHtml = '<div class="review-card-photos">';
                for (var p = 0; p < review.photos.length; p++) {
                    var photo = review.photos[p];
                    photosHtml += '<div class="review-card-photo" data-review-id="' + review.id + '" data-photo-index="' + p + '">' +
                        '<img src="' + this.escapeHtml(photo.file_path) + '" alt="' + this.escapeHtml(photo.file_name || '리뷰 사진') + '" loading="lazy">' +
                    '</div>';
                }
                photosHtml += '</div>';
            }

            // 좋아요
            var likesCount = parseInt(review.likes_count) || 0;
            var likeHtml = '<button type="button" class="review-like-btn" data-review-id="' + review.id + '">' +
                '👍 도움이 돼요 <span class="review-like-count">' + likesCount + '</span>' +
            '</button>';

            // 관리자 답변
            var replyHtml = '';
            if (review.admin_reply) {
                var replyDate = review.admin_reply_at ? this.formatDate(review.admin_reply_at) : '';
                replyHtml = '<div class="review-admin-reply">' +
                    '<div class="review-admin-reply-label">사장님 답변</div>' +
                    '<div class="review-admin-reply-text">' + this.escapeHtml(review.admin_reply) + '</div>' +
                    (replyDate ? '<div class="review-admin-reply-date">' + replyDate + '</div>' : '') +
                '</div>';
            }

            return '<div class="review-card" data-id="' + review.id + '">' +
                '<div class="review-card-header">' +
                    '<span class="review-card-stars">' + starsHtml + '</span>' +
                    '<span class="review-card-user">' + this.escapeHtml(maskedName) + '</span>' +
                    verifiedHtml +
                    '<span class="review-card-date">' + this.escapeHtml(timeAgoStr) + '</span>' +
                '</div>' +
                title +
                content +
                photosHtml +
                '<div class="review-card-footer">' + likeHtml + '</div>' +
                replyHtml +
            '</div>';
        },

        bindReviewEvents: function(container) {
            var self = this;

            // 좋아요 버튼
            var likeBtns = container.querySelectorAll('.review-like-btn');
            for (var i = 0; i < likeBtns.length; i++) {
                (function(btn) {
                    btn.addEventListener('click', function() {
                        var reviewId = parseInt(this.getAttribute('data-review-id'));
                        self.toggleLike(reviewId, this);
                    });
                })(likeBtns[i]);
            }

            // 사진 클릭 → 라이트박스
            var photos = container.querySelectorAll('.review-card-photo');
            for (var j = 0; j < photos.length; j++) {
                (function(photoEl) {
                    photoEl.addEventListener('click', function() {
                        var reviewId = this.getAttribute('data-review-id');
                        var photoIndex = parseInt(this.getAttribute('data-photo-index'));
                        var card = container.querySelector('.review-card[data-id="' + reviewId + '"]');
                        if (!card) return;

                        var allPhotos = card.querySelectorAll('.review-card-photo img');
                        var photoList = [];
                        for (var k = 0; k < allPhotos.length; k++) {
                            photoList.push({
                                src: allPhotos[k].src,
                                alt: allPhotos[k].alt || '리뷰 사진'
                            });
                        }
                        self.openLightbox(photoList, photoIndex);
                    });
                })(photos[j]);
            }
        },

        // ─── 별점 표시 ───
        renderStarsHtml: function(rating) {
            var full = Math.floor(rating);
            var hasHalf = (rating - full) >= 0.3;
            var html = '';
            for (var i = 1; i <= 5; i++) {
                if (i <= full) {
                    html += '★';
                } else if (i === full + 1 && hasHalf) {
                    html += '★'; // 반별 대신 별 하나로 표시 (심플)
                } else {
                    html += '☆';
                }
            }
            return html;
        },

        // ─── 별점 인터렉션 ───
        previewStars: function(rating) {
            var starBtns = document.querySelectorAll('#reviewStarSelector .review-star-btn');
            for (var i = 0; i < starBtns.length; i++) {
                var r = parseInt(starBtns[i].getAttribute('data-rating'));
                if (r <= rating) {
                    starBtns[i].classList.remove('empty');
                } else {
                    starBtns[i].classList.add('empty');
                }
            }
        },

        setRating: function(rating) {
            this.selectedRating = rating;
            this.previewStars(rating);
            var input = document.getElementById('reviewRatingInput');
            if (input) input.value = rating;
            var text = document.getElementById('reviewStarText');
            if (text) text.textContent = rating + '점';
        },

        // ─── 작성 폼 토글 ───
        toggleWriteForm: function() {
            var wrapper = document.getElementById('reviewFormWrapper');
            if (!wrapper) return;
            if (wrapper.style.display === 'none' || wrapper.style.display === '') {
                wrapper.style.display = 'block';
                wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                this.hideWriteForm();
            }
        },

        hideWriteForm: function() {
            var wrapper = document.getElementById('reviewFormWrapper');
            if (wrapper) wrapper.style.display = 'none';
        },

        // ─── 사진 업로드 핸들링 ───
        handlePhotoUpload: function(input) {
            var maxPhotos = 5;
            var maxSize = 5 * 1024 * 1024; // 5MB
            var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            var files = input.files;

            for (var i = 0; i < files.length; i++) {
                if (this.pendingPhotos.length >= maxPhotos) {
                    this.showToast('사진은 최대 ' + maxPhotos + '장까지 첨부할 수 있습니다.', 'error');
                    break;
                }
                if (files[i].size > maxSize) {
                    this.showToast('"' + files[i].name + '" 파일이 5MB를 초과합니다.', 'error');
                    continue;
                }
                if (allowedTypes.indexOf(files[i].type) === -1) {
                    this.showToast('JPG, PNG, WEBP 형식만 업로드 가능합니다.', 'error');
                    continue;
                }
                this.pendingPhotos.push(files[i]);
            }

            // input 초기화 (같은 파일 재선택 가능)
            input.value = '';
            this.renderPhotoPreviews();
        },

        renderPhotoPreviews: function() {
            var container = document.getElementById('reviewPhotoPreviews');
            var addLabel = document.getElementById('reviewPhotoAddLabel');
            if (!container) return;

            container.innerHTML = '';
            var self = this;

            for (var i = 0; i < this.pendingPhotos.length; i++) {
                (function(index) {
                    var div = document.createElement('div');
                    div.className = 'review-photo-preview';

                    var img = document.createElement('img');
                    img.alt = '미리보기';
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(self.pendingPhotos[index]);

                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'review-photo-remove';
                    removeBtn.textContent = '×';
                    removeBtn.addEventListener('click', function() {
                        self.removePhoto(index);
                    });

                    div.appendChild(img);
                    div.appendChild(removeBtn);
                    container.appendChild(div);
                })(i);
            }

            // 5장 이상이면 추가 버튼 숨기기
            if (addLabel) {
                addLabel.style.display = this.pendingPhotos.length >= 5 ? 'none' : 'flex';
            }
        },

        removePhoto: function(index) {
            this.pendingPhotos.splice(index, 1);
            this.renderPhotoPreviews();
        },

        // ─── 리뷰 제출 ───
        submitReview: function() {
            var self = this;
            var content = (document.getElementById('reviewContent') || {}).value || '';
            if (!content.trim()) {
                this.showToast('리뷰 내용을 입력해주세요.', 'error');
                return;
            }

            var submitBtn = document.getElementById('reviewSubmitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = '등록 중...';
            }

            var formData = new FormData();
            formData.append('action', 'create');
            formData.append('product_type', this.productType);
            formData.append('rating', this.selectedRating);
            formData.append('title', (document.getElementById('reviewTitle') || {}).value || '');
            formData.append('content', content);

            var orderId = (document.getElementById('reviewOrderId') || {}).value || '';
            if (orderId) formData.append('order_id', orderId);

            for (var i = 0; i < this.pendingPhotos.length; i++) {
                formData.append('photos[]', this.pendingPhotos[i]);
            }

            fetch(this.apiBase, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (json.success) {
                    self.showToast('리뷰가 등록되었습니다. 관리자 승인 후 게시됩니다.', 'success');
                    self.resetForm();
                    self.hideWriteForm();
                } else {
                    self.showToast(json.error || '리뷰 등록에 실패했습니다.', 'error');
                }
            })
            .catch(function() {
                self.showToast('네트워크 오류가 발생했습니다.', 'error');
            })
            .finally(function() {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '리뷰 등록';
                }
            });
        },

        resetForm: function() {
            var form = document.getElementById('reviewForm');
            if (form) form.reset();
            this.selectedRating = 5;
            this.previewStars(5);
            this.pendingPhotos = [];
            this.renderPhotoPreviews();
            var countEl = document.getElementById('reviewCharCount');
            if (countEl) countEl.textContent = '0';
            var starText = document.getElementById('reviewStarText');
            if (starText) starText.textContent = '5점';
            var ratingInput = document.getElementById('reviewRatingInput');
            if (ratingInput) ratingInput.value = '5';
        },

        // ─── 좋아요 토글 ───
        toggleLike: function(reviewId, btn) {
            var self = this;
            if (btn.disabled) return;
            btn.disabled = true;

            var formData = new FormData();
            formData.append('action', 'like');
            formData.append('review_id', reviewId);

            fetch(this.apiBase, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (json.success) {
                    var countSpan = btn.querySelector('.review-like-count');
                    if (countSpan) countSpan.textContent = json.data.likes_count;
                    if (json.data.liked) {
                        btn.classList.add('liked');
                    } else {
                        btn.classList.remove('liked');
                    }
                } else {
                    self.showToast(json.error || '오류가 발생했습니다.', 'error');
                }
            })
            .catch(function() {
                self.showToast('네트워크 오류가 발생했습니다.', 'error');
            })
            .finally(function() {
                btn.disabled = false;
            });
        },

        // ─── 페이지네이션 ───
        renderPagination: function(total, page, perPage) {
            var paginationEl = document.getElementById('reviewPagination');
            if (!paginationEl) return;

            var totalPages = Math.ceil(total / perPage);
            if (totalPages <= 1) {
                paginationEl.innerHTML = '';
                return;
            }

            var self = this;
            var html = '';

            // 이전 버튼
            html += '<button type="button" class="review-page-btn" data-page="' + (page - 1) + '"' +
                (page <= 1 ? ' disabled' : '') + '>‹</button>';

            // 페이지 번호 (최대 5개씩)
            var startPage = Math.max(1, page - 2);
            var endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            for (var p = startPage; p <= endPage; p++) {
                html += '<button type="button" class="review-page-btn' +
                    (p === page ? ' active' : '') +
                    '" data-page="' + p + '">' + p + '</button>';
            }

            // 다음 버튼
            html += '<button type="button" class="review-page-btn" data-page="' + (page + 1) + '"' +
                (page >= totalPages ? ' disabled' : '') + '>›</button>';

            paginationEl.innerHTML = html;

            // 이벤트 바인딩
            var pageBtns = paginationEl.querySelectorAll('.review-page-btn');
            for (var i = 0; i < pageBtns.length; i++) {
                (function(btn) {
                    btn.addEventListener('click', function() {
                        if (this.disabled) return;
                        var targetPage = parseInt(this.getAttribute('data-page'));
                        self.loadReviews(targetPage, self.currentSort);
                        // 리뷰 목록 상단으로 스크롤
                        var widget = document.getElementById('reviewWidget');
                        if (widget) widget.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                })(pageBtns[i]);
            }
        },

        // ─── 라이트박스 ───
        openLightbox: function(photos, index) {
            this.lightboxPhotos = photos || [];
            this.lightboxIndex = index || 0;

            var lightbox = document.getElementById('reviewLightbox');
            if (!lightbox || this.lightboxPhotos.length === 0) return;

            lightbox.style.display = 'block';
            document.body.style.overflow = 'hidden';
            this.updateLightboxImage();
        },

        closeLightbox: function() {
            var lightbox = document.getElementById('reviewLightbox');
            if (!lightbox || lightbox.style.display === 'none') return;
            lightbox.style.display = 'none';
            document.body.style.overflow = '';
            this.lightboxPhotos = [];
        },

        lightboxPrev: function() {
            if (this.lightboxPhotos.length === 0) return;
            this.lightboxIndex = (this.lightboxIndex - 1 + this.lightboxPhotos.length) % this.lightboxPhotos.length;
            this.updateLightboxImage();
        },

        lightboxNext: function() {
            if (this.lightboxPhotos.length === 0) return;
            this.lightboxIndex = (this.lightboxIndex + 1) % this.lightboxPhotos.length;
            this.updateLightboxImage();
        },

        updateLightboxImage: function() {
            var img = document.getElementById('reviewLightboxImg');
            var counter = document.getElementById('reviewLightboxCounter');
            if (!img) return;

            var photo = this.lightboxPhotos[this.lightboxIndex];
            if (photo) {
                img.src = photo.src;
                img.alt = photo.alt || '리뷰 사진';
            }

            if (counter) {
                counter.textContent = (this.lightboxIndex + 1) + ' / ' + this.lightboxPhotos.length;
            }

            // prev/next 버튼 표시 (1장이면 숨김)
            var lightbox = document.getElementById('reviewLightbox');
            if (lightbox) {
                var prevBtn = lightbox.querySelector('.review-lightbox-prev');
                var nextBtn = lightbox.querySelector('.review-lightbox-next');
                var show = this.lightboxPhotos.length > 1;
                if (prevBtn) prevBtn.style.display = show ? 'flex' : 'none';
                if (nextBtn) nextBtn.style.display = show ? 'flex' : 'none';
                if (counter) counter.style.display = show ? 'block' : 'none';
            }
        },

        // ─── 유틸리티 ───
        maskName: function(name) {
            if (!name) return '익명';
            if (name.length <= 1) return name;
            return name.charAt(0) + '**';
        },

        timeAgo: function(dateStr) {
            if (!dateStr) return '';
            var date = new Date(dateStr.replace(/-/g, '/'));  // Safari 호환
            var now = new Date();
            var diff = Math.floor((now - date) / 1000);

            if (diff < 60) return '방금 전';
            if (diff < 3600) return Math.floor(diff / 60) + '분 전';
            if (diff < 86400) return Math.floor(diff / 3600) + '시간 전';
            if (diff < 2592000) return Math.floor(diff / 86400) + '일 전';
            if (diff < 31536000) return Math.floor(diff / 2592000) + '개월 전';
            return Math.floor(diff / 31536000) + '년 전';
        },

        formatDate: function(dateStr) {
            if (!dateStr) return '';
            var d = new Date(dateStr.replace(/-/g, '/'));
            var y = d.getFullYear();
            var m = ('0' + (d.getMonth() + 1)).slice(-2);
            var day = ('0' + d.getDate()).slice(-2);
            return y + '.' + m + '.' + day;
        },

        escapeHtml: function(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        },

        showToast: function(message, type) {
            var toast = document.getElementById('reviewToast');
            if (!toast) return;

            toast.textContent = message;
            toast.className = 'review-toast ' + (type || 'success');
            toast.style.display = 'block';

            clearTimeout(this._toastTimer);
            this._toastTimer = setTimeout(function() {
                toast.style.display = 'none';
            }, 3000);
        }
    };

    // 초기화
    document.addEventListener('DOMContentLoaded', function() {
        ReviewWidget.init();
    });
})();
