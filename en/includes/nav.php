<?php
$_en_current_page = isset($_en_current_page) ? $_en_current_page : '';
$_en_products = [
    ['key' => 'sticker',         'label' => 'Stickers',         'href' => '/en/products/order_sticker.php'],
    ['key' => 'inserted',        'label' => 'Flyers',           'href' => '/en/products/order.php?type=inserted'],
    ['key' => 'namecard',        'label' => 'Business Cards',   'href' => '/en/products/order.php?type=namecard'],
    ['key' => 'envelope',        'label' => 'Envelopes',        'href' => '/en/products/order.php?type=envelope'],
    ['key' => 'cadarok',         'label' => 'Catalogs',         'href' => '/en/products/order.php?type=cadarok'],
    ['key' => 'littleprint',     'label' => 'Posters',          'href' => '/en/products/order.php?type=littleprint'],
    ['key' => 'ncrflambeau',     'label' => 'NCR Forms',        'href' => '/en/products/order.php?type=ncrflambeau'],
    ['key' => 'merchandisebond', 'label' => 'Gift Vouchers',    'href' => '/en/products/order.php?type=merchandisebond'],
    ['key' => 'msticker',        'label' => 'Magnetic Stickers','href' => '/en/products/order.php?type=msticker'],
];
?>
<style>
.en-nav{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,0.92);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);transition:box-shadow .3s}
.en-nav.scrolled{box-shadow:0 2px 20px rgba(0,0,0,0.06)}
.en-nav-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:0 24px;height:64px}
.en-nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.en-nav-logo img{height:36px;width:auto}
.en-nav-logo span{font-family:var(--font-heading);font-weight:700;font-size:18px;color:var(--navy);letter-spacing:-.5px}
.en-nav-links{display:flex;align-items:center;gap:28px}
.en-nav-links a{font-size:14px;font-weight:500;color:var(--text-muted);text-decoration:none;transition:color .2s}
.en-nav-links a:hover,.en-nav-links a.active{color:var(--navy)}
.en-nav-lang{font-size:13px;color:var(--text-light);display:flex;align-items:center;gap:6px}
.en-nav-lang a{color:var(--navy);font-weight:600;text-decoration:none}
.en-nav-cta{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border-radius:10px;background:var(--navy);color:var(--white);font-size:14px;font-weight:600;text-decoration:none;transition:all .25s;border:none;cursor:pointer}
.en-nav-cta:hover{background:var(--navy-dark);transform:translateY(-1px);box-shadow:var(--shadow-md)}
.en-nav-toggle{display:none;background:none;border:none;cursor:pointer;width:32px;height:32px;position:relative}
.en-nav-toggle span{display:block;width:22px;height:2px;background:var(--text);position:absolute;left:5px;transition:all .3s}
.en-nav-toggle span:nth-child(1){top:9px}
.en-nav-toggle span:nth-child(2){top:15px}
.en-nav-toggle span:nth-child(3){top:21px}

.en-pbar{position:fixed;top:64px;left:0;right:0;z-index:99;background:var(--white);border-bottom:1px solid var(--border);height:44px;display:flex;align-items:center}
.en-pbar-inner{max-width:1200px;margin:0 auto;padding:0 24px;display:flex;align-items:center;gap:6px;width:100%;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none}
.en-pbar-inner::-webkit-scrollbar{display:none}
.en-pbar-btn{flex-shrink:0;padding:6px 14px;border-radius:8px;font-size:13px;font-weight:500;color:var(--text-muted);text-decoration:none;transition:all .2s;white-space:nowrap;border:none;background:none;cursor:pointer;font-family:var(--font-body)}
.en-pbar-btn:hover{background:rgba(30,78,121,0.08);color:var(--navy)}
.en-pbar-btn.active{background:var(--navy);color:var(--white)}
.en-pbar-cart{flex-shrink:0;margin-left:auto;padding:6px 14px;border-radius:8px;font-size:13px;font-weight:600;color:var(--navy);text-decoration:none;transition:all .2s;white-space:nowrap;display:inline-flex;align-items:center;gap:5px}
.en-pbar-cart:hover{background:rgba(30,78,121,0.08)}
.en-pbar-cart.active{background:var(--navy);color:var(--white)}

@media(max-width:768px){
    .en-nav-links{display:none}
    .en-nav-toggle{display:block}
    .en-nav-links.open{display:flex;flex-direction:column;position:absolute;top:64px;left:0;right:0;background:var(--white);padding:20px 24px;border-bottom:1px solid var(--border);box-shadow:var(--shadow-md);gap:16px;z-index:101}
    .en-pbar{top:64px}
    .en-pbar-inner{padding:0 12px;gap:4px}
    .en-pbar-btn{padding:5px 10px;font-size:12px}
}
</style>

<nav class="en-nav" id="enNav">
    <div class="en-nav-inner">
        <a href="/en/" class="en-nav-logo">
            <img src="/ImgFolder/dusonlogo1.png" alt="Duson Print">
            <span>DUSON PRINT</span>
        </a>
        <div class="en-nav-links" id="enNavLinks">
            <a href="/en/products/">Products</a>
            <a href="/en/cart.php">Cart</a>
            <a href="/en/#why-us">Why Us</a>
            <a href="/en/#quote">Contact</a>
            <span class="en-nav-lang">EN | <a href="/">한국어</a></span>
            <a href="/en/#quote" class="en-nav-cta">Get Free Quote</a>
        </div>
        <button class="en-nav-toggle" id="enNavToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<div class="en-pbar">
    <div class="en-pbar-inner">
        <?php foreach ($_en_products as $p): ?>
        <a href="<?php echo $p['href']; ?>" class="en-pbar-btn<?php echo ($_en_current_page === $p['key']) ? ' active' : ''; ?>"><?php echo $p['label']; ?></a>
        <?php endforeach; ?>
        <a href="/en/cart.php" class="en-pbar-cart<?php echo ($_en_current_page === 'cart') ? ' active' : ''; ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
            Cart
        </a>
    </div>
</div>

<script>
(function(){
    window.addEventListener('scroll',function(){
        var n=document.getElementById('enNav');
        if(n)n.classList.toggle('scrolled',window.scrollY>10);
    });
    var t=document.getElementById('enNavToggle');
    if(t)t.addEventListener('click',function(){
        document.getElementById('enNavLinks').classList.toggle('open');
    });
})();
</script>
