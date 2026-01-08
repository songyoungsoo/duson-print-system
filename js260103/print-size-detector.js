/**
 * 인쇄 규격 자동 감지 엔진 (DB 연동)
 *
 * DB에서 규격 데이터를 가져와서 사용
 * 관리자가 규격을 추가/수정하면 자동 반영
 *
 * @version 1.0
 * @date 2025-12-03
 */

const PrintSizeDetector = {
    // 규격 데이터 (DB에서 로드)
    sizes: [],

    // 1연 = 전지 500장
    SHEETS_PER_YEON: 500,

    // 초기화 상태
    initialized: false,

    /**
     * DB에서 규격 데이터 로드
     * @param {string} series - 'A', 'B', 또는 null (전체)
     * @returns {Promise}
     */
    async init(series = null) {
        try {
            let url = '/api/get_print_sizes.php';
            if (series) {
                url += `?series=${series}`;
            }

            const response = await fetch(url);
            const result = await response.json();

            if (result.success) {
                this.sizes = result.data.sizes;
                this.SHEETS_PER_YEON = result.data.sheets_per_yeon_base || 500;
                this.initialized = true;
                console.log(`✅ PrintSizeDetector 초기화 완료: ${this.sizes.length}개 규격 로드`);
                return true;
            } else {
                throw new Error(result.error || '규격 로드 실패');
            }
        } catch (error) {
            console.error('❌ PrintSizeDetector 초기화 실패:', error);
            // 폴백: 기본 규격 사용
            this.loadDefaultSizes();
            return false;
        }
    },

    /**
     * 폴백용 기본 규격 (DB 연결 실패 시)
     */
    loadDefaultSizes() {
        this.sizes = [
            { name: 'A1', width: 594, height: 841, jeolsu: 1, series: 'A', sheets_per_yeon: 500 },
            { name: 'A2', width: 420, height: 594, jeolsu: 2, series: 'A', sheets_per_yeon: 1000 },
            { name: 'A3', width: 297, height: 420, jeolsu: 4, series: 'A', sheets_per_yeon: 2000 },
            { name: 'A4', width: 210, height: 297, jeolsu: 8, series: 'A', sheets_per_yeon: 4000 },
            { name: 'A5', width: 148, height: 210, jeolsu: 16, series: 'A', sheets_per_yeon: 8000 },
            { name: 'A6', width: 105, height: 148, jeolsu: 32, series: 'A', sheets_per_yeon: 16000 },
            { name: 'B1', width: 728, height: 1030, jeolsu: 1, series: 'B', sheets_per_yeon: 500 },
            { name: 'B2', width: 515, height: 728, jeolsu: 2, series: 'B', sheets_per_yeon: 1000 },
            { name: 'B3', width: 364, height: 515, jeolsu: 4, series: 'B', sheets_per_yeon: 2000 },
            { name: 'B4', width: 257, height: 364, jeolsu: 8, series: 'B', sheets_per_yeon: 4000 },
            { name: 'B5', width: 182, height: 257, jeolsu: 16, series: 'B', sheets_per_yeon: 8000 },
            { name: 'B6', width: 128, height: 182, jeolsu: 32, series: 'B', sheets_per_yeon: 16000 }
        ];
        this.initialized = true;
        console.log('⚠️ PrintSizeDetector: 기본 규격 사용 (DB 연결 실패)');
    },

    /**
     * 가장 가까운 규격 찾기 (서버 API 사용)
     * @param {number} width - 가로 (mm)
     * @param {number} height - 세로 (mm)
     * @returns {Promise<object>} 매칭 결과
     */
    async findClosestFromServer(width, height) {
        try {
            const url = `/api/get_print_sizes.php?find=1&width=${width}&height=${height}`;
            const response = await fetch(url);
            const result = await response.json();

            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('서버 API 오류, 로컬 계산 사용:', error);
            return this.findClosest(width, height);
        }
    },

    /**
     * 가장 가까운 규격 찾기 (로컬 계산)
     * @param {number} width - 가로 (mm)
     * @param {number} height - 세로 (mm)
     * @returns {object} 매칭 결과
     */
    findClosest(width, height) {
        if (!this.initialized || this.sizes.length === 0) {
            this.loadDefaultSizes();
        }

        // 정규화 (작은값=가로, 큰값=세로)
        const w = Math.min(width, height);
        const h = Math.max(width, height);

        let bestMatch = null;
        let minDiff = Infinity;

        for (const spec of this.sizes) {
            const diffW = Math.abs(spec.width - w);
            const diffH = Math.abs(spec.height - h);
            const totalDiff = Math.sqrt(diffW * diffW + diffH * diffH);

            if (totalDiff < minDiff) {
                minDiff = totalDiff;
                bestMatch = {
                    ...spec,
                    diff_width: diffW,
                    diff_height: diffH,
                    total_diff: Math.round(totalDiff * 10) / 10
                };
            }
        }

        // 재단 가능 여부
        const canCut = (w <= bestMatch.width && h <= bestMatch.height);

        // 메시지 생성
        let message = `${bestMatch.name} (${bestMatch.width}×${bestMatch.height}mm)`;
        if (canCut) {
            message += ' - 재단 가능';
            if (bestMatch.diff_width > 0 || bestMatch.diff_height > 0) {
                message += ` (여백: 가로 ${bestMatch.diff_width}mm, 세로 ${bestMatch.diff_height}mm)`;
            }
        } else {
            message += ' - ⚠️ 입력 크기가 규격보다 큼';
        }
        message += ` | ${bestMatch.jeolsu}절 = 1연당 ${bestMatch.sheets_per_yeon.toLocaleString()}장`;

        return {
            input: { width: w, height: h },
            match: bestMatch,
            can_cut: canCut,
            message: message
        };
    },

    /**
     * 매수 계산
     * @param {number} yeon - 연수 (0.5, 1, 2 등)
     * @param {number} jeolsu - 절수
     * @returns {number} 매수
     */
    calculateSheets(yeon, jeolsu) {
        return Math.floor(this.SHEETS_PER_YEON * jeolsu * yeon);
    },

    /**
     * 모든 규격 반환 (드롭다운용)
     * @returns {array}
     */
    getAllSizes() {
        if (!this.initialized) {
            this.loadDefaultSizes();
        }
        return this.sizes.map(spec => ({
            ...spec,
            label: `${spec.name} (${spec.width}×${spec.height}mm) - ${spec.jeolsu}절`
        }));
    },

    /**
     * 시리즈별 규격 반환
     * @param {string} series - 'A' 또는 'B'
     * @returns {array}
     */
    getSizesBySeries(series) {
        return this.getAllSizes().filter(s => s.series === series.toUpperCase());
    }
};

// 전역 노출
window.PrintSizeDetector = PrintSizeDetector;

// 페이지 로드 시 자동 초기화 (필요시)
// document.addEventListener('DOMContentLoaded', () => PrintSizeDetector.init());
