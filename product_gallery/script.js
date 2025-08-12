
const mainImage = document.getElementById('mainImage');
const zoomMask = document.getElementById('zoomMask');
const zoomView = document.getElementById('zoomView');
const thumbnails = document.querySelectorAll('.thumb');

const zoomRatio = 1.8; // Adjusted zoom ratio

function updateZoomImage(src) {
  zoomView.style.backgroundImage = `url('${src}')`;
  console.log("Zoom image updated to:", src);
}

thumbnails.forEach(thumb => {
  thumb.addEventListener('click', () => {
    const bigSrc = thumb.getAttribute('data-big');
    mainImage.src = bigSrc;
    updateZoomImage(bigSrc);
  });
});

mainImage.addEventListener('mouseenter', () => {
  zoomMask.style.display = 'block';
  zoomView.style.display = 'block';
  updateZoomImage(mainImage.src);
});

mainImage.addEventListener('mouseleave', () => {
  zoomMask.style.display = 'none';
  zoomView.style.display = 'none';
});

mainImage.addEventListener('mousemove', e => {
  const rect = mainImage.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;

  const maskSize = 100;
  let left = x - maskSize / 2;
  let top = y - maskSize / 2;

  if (left < 0) left = 0;
  if (top < 0) top = 0;
  if (left > rect.width - maskSize) left = rect.width - maskSize;
  if (top > rect.height - maskSize) top = rect.height - maskSize;

  zoomMask.style.left = `${left}px`;
  zoomMask.style.top = `${top}px`;

  const bgX = Math.min(left * zoomRatio, rect.width * (zoomRatio - 1));
  const bgY = Math.min(top * zoomRatio, rect.height * (zoomRatio - 1));

  zoomView.style.backgroundSize = `${rect.width * zoomRatio}px ${rect.height * zoomRatio}px`;
  zoomView.style.backgroundPosition = `-${bgX}px -${bgY}px`;
});
