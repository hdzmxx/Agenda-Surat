<?php
?>
<!doctype html>
<?php helper('website'); $website = website_settings(); ?>
<html lang="id" data-bs-theme="<?= esc($website['mode']) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk | <?= esc($website['browser_title']) ?></title>

  <?php if ($website['favicon'] !== null): ?>
    <link rel="icon" type="image/png" href="<?= site_url('website/favicon') ?>?v=<?= esc(rawurlencode($website['favicon'])) ?>">
  <?php endif ?>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --login-primary: <?= esc($website['primary_color']) ?>;
      --login-primary-rgb: <?= esc(website_color_rgb($website['primary_color'])) ?>;
      --login-water: #062431;
    }

    body {
      min-height: 100vh;
      min-height: 100svh;
      overflow: hidden;
      color: #f8fafc;
      background-color: var(--login-water);
      background-image:
        radial-gradient(ellipse at 48% -18%, rgba(48, 135, 151, .32), transparent 65%),
        linear-gradient(165deg, #6de873 0%, #0a6b98 48%, #0c1e2d 100%);
    }

    .koi-background {
      position: fixed;
      inset: 0;
      z-index: 0;
      overflow: hidden;
      pointer-events: none;
    }

    .koi-canvas {
      position: relative;
      z-index: 1;
      display: block;
      width: 100%;
      height: 100%;
    }

    .login-wrapper {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 440px;
    }

    .login-card {
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, .2);
      border-radius: 1.5rem;
      background: rgba(255, 255, 255, .09);
      box-shadow: 0 1.5rem 4rem rgba(0, 0, 0, .38);
      -webkit-backdrop-filter: blur(24px);
      backdrop-filter: blur(24px);
    }

    .login-header {
      padding: 2.25rem 2rem 1.25rem;
      text-align: center;
    }

    .login-logo-wrap {
      display: grid;
      width: 68px;
      height: 68px;
      margin: 0 auto 1.5rem;
      place-items: center;
      border: 1px solid rgba(255, 255, 255, .24);
      border-radius: 50%;
      background: linear-gradient(145deg, var(--login-primary), #e84f36);
      box-shadow: 0 .6rem 1.8rem rgba(var(--login-primary-rgb), .28);
    }

    /* Logo unggahan tampil langsung sesuai bentuk aslinya, tanpa lingkaran. */
    .login-logo {
      display: block;
      width: auto;
      height: auto;
      max-width: 220px;
      max-height: 84px;
      margin: 0 auto 1.5rem;
      object-fit: contain;
      filter: drop-shadow(0 .35rem .9rem rgba(0, 0, 0, .28));
    }

    .login-lock-icon {
      width: 30px;
      height: 30px;
      color: #fff;
    }

    .login-subtitle {
      color: rgba(255, 255, 255, .62);
      font-size: .9rem;
    }

    .login-content {
      padding: .25rem 2rem 2rem;
    }

    .login-card .form-label {
      color: rgba(255, 255, 255, .82);
      font-size: .9rem;
    }

    .login-input-wrap {
      position: relative;
    }

    .login-input-icon {
      position: absolute;
      top: 50%;
      left: 1rem;
      z-index: 2;
      width: 1.15rem;
      height: 1.15rem;
      color: rgba(255, 255, 255, .48);
      pointer-events: none;
      transform: translateY(-50%);
    }

    .form-control {
      min-height: 50px;
      border: 1px solid rgba(255, 255, 255, .14);
      border-radius: .9rem;
      background: rgba(255, 255, 255, .055);
      color: #fff;
      box-shadow: inset 0 1px 2px rgba(25, 22, 22, 0.12);
    }

    .form-control:focus {
      border-color: rgba(var(--login-primary-rgb), .8);
      background: rgba(255, 255, 255, .1);
      color: #fff;
      box-shadow: 0 0 0 .2rem rgba(var(--login-primary-rgb), .2);
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, .34);
    }

    .form-control:-webkit-autofill,
    .form-control:-webkit-autofill:hover,
    .form-control:-webkit-autofill:focus {
      -webkit-text-fill-color: #fff;
      -webkit-box-shadow: 0 0 0 40px #10252c inset;
      transition: background-color 5000s ease-in-out 0s;
    }

    .btn-login {
      min-height: 52px;
      border: 0;
      border-radius: .9rem;
      background: linear-gradient(110deg, var(--login-primary), #09017c);
      color: #fff;
      font-weight: 600;
      box-shadow: 0 .6rem 1.6rem rgba(var(--login-primary-rgb), .24);
      transition: filter .2s ease, transform .2s ease, box-shadow .2s ease;
    }

    .btn-login:hover {
      background: linear-gradient(110deg, var(--login-primary), #e6533c);
      color: #fff;
      filter: brightness(1.08);
      box-shadow: 0 .8rem 1.8rem rgba(var(--login-primary-rgb), .32);
      transform: translateY(-2px);
    }

    .login-footer {
      color: rgba(255, 255, 255, .55);
    }

    .login-card .alert {
      border-color: rgba(255, 145, 145, .3);
      border-radius: .85rem;
      background: rgba(160, 34, 42, .28);
      color: #ffe2e2;
    }

    @media (max-width: 576px) {
      .login-header { padding: 1.75rem 1.5rem 1rem; }
      .login-content { padding: .25rem 1.5rem 1.5rem; }
      .login-logo-wrap { width: 60px; height: 60px; margin-bottom: 1.25rem; }
      .login-logo { max-width: 180px; max-height: 68px; margin-bottom: 1.25rem; }
    }

    @media (max-height: 650px) {
      body {
        align-items: flex-start !important;
        overflow-y: auto;
      }
      .login-wrapper { margin-block: auto; }
      .login-header { padding-top: 1.25rem; }
      .login-logo-wrap { width: 52px; height: 52px; margin-bottom: .9rem; }
      .login-logo { max-width: 150px; max-height: 54px; margin-bottom: .9rem; }
      .login-content { padding-bottom: 1.25rem; }
      .login-footer { margin-top: 1rem !important; }
    }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { scroll-behavior: auto !important; }
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
  <div class="koi-background" aria-hidden="true">
    <canvas class="koi-canvas" id="koiCanvas"></canvas>
  </div>

  <main class="login-wrapper">
    <section class="login-card">
      <header class="login-header">
        <?php if ($website['logo'] !== null): ?>
          <img class="login-logo" src="<?= site_url('website/logo') ?>" alt="Logo aplikasi">
        <?php else: ?>
          <div class="login-logo-wrap">
            <svg class="login-lock-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <rect x="4" y="10" width="16" height="11" rx="2"/>
              <path d="M8 10V7a4 4 0 1 1 8 0v3m-4 5v2" stroke-linecap="round"/>
            </svg>
          </div>
        <?php endif ?>

        <h1 class="h3 fw-bold mb-2">Selamat Datang</h1>
        <p class="login-subtitle mb-0"><?= esc($website['app_name']) ?> | <?= esc(env('INSTANSI', 'Sekolah')) ?></p>
      </header>

      <div class="login-content">
        <?php if ($m = session()->getFlashdata('error')): ?>
          <div class="alert alert-danger py-2" role="alert"><?= esc($m) ?></div>
        <?php endif ?>

        <form method="post" action="<?= site_url('login') ?>">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label fw-semibold" for="username">Nama Pengguna</label>
            <div class="login-input-wrap">
              <svg class="login-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
              </svg>
              <input
                class="form-control ps-5"
                id="username"
                name="username"
                value="<?= esc(old('username')) ?>"
                placeholder="Masukkan nama pengguna"
                autocomplete="username"
                required
              >
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold" for="password">Kata Sandi</label>
            <div class="login-input-wrap">
              <svg class="login-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
              </svg>
              <input
                class="form-control ps-5 pe-5"
                id="password"
                type="password"
                name="password"
                placeholder="Masukkan kata sandi"
                autocomplete="current-password"
                required
              >
              <button type="button" id="togglePassword" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-decoration-none" style="z-index: 3; color: rgba(255, 255, 255, .48); box-shadow: none;">
                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                  <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                </svg>
              </button>
            </div>
            <div id="capsLockWarning" class="form-text mt-1" style="display: none; color: #facc15; font-size: 0.85rem;">
              ⚠️ Caps Lock sedang aktif
            </div>
          </div>

          <button class="btn btn-login w-100" type="submit">Masuk</button>
        </form>
      </div>
    </section>

    <p class="login-footer text-center small mt-4 mb-0">
      &copy; <?= date('Y') ?> <?= esc($website['app_name']) ?> by hdzmxx
    </p>
  </main>
  <script>
    if (window.innerWidth > 576 && window.innerHeight > 650) {
      document.getElementById('username').focus({ preventScroll: true });
    }

    // --- FITUR CAPS LOCK & INTIP PASSWORD ---
    const passwordInput = document.getElementById('password');
    const capsLockWarning = document.getElementById('capsLockWarning');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');

    passwordInput.addEventListener('keyup', function(event) {
      if (event.getModifierState('CapsLock')) {
        capsLockWarning.style.display = 'block';
      } else {
        capsLockWarning.style.display = 'none';
      }
    });

    passwordInput.addEventListener('mousedown', function(event) {
      if (event.getModifierState && event.getModifierState('CapsLock')) {
        capsLockWarning.style.display = 'block';
      }
    });

    togglePasswordBtn.addEventListener('click', function () {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      if (type === 'text') {
        eyeIcon.innerHTML = '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755l-.809-.805zm-4.321-4.322a2.5 2.5 0 0 0-3.414 3.414l3.414-3.414zm-1.077 4.542l-1.07-1.07a2.5 2.5 0 0 1-3.23-3.23l-1.07-1.07A13.13 13.13 0 0 0 1.172 8c.058.087.122.183.195.288.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.771A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l1.092 1.092C2.42 7.018 1.458 8 1.458 8c.11.164.24.354.39.567.433.61 1.026 1.365 1.84 2.03C4.945 11.666 6.388 12.5 8 12.5c.348 0 .684-.04 1.009-.115l1.05 1.05z"/>';
      } else {
        eyeIcon.innerHTML = '<path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>';
      }
    });
    // --- AKHIR FITUR ---

    const canvas = document.getElementById('koiCanvas');
    const context = canvas.getContext('2d');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const pointer = { x: -1000, y: -1000 };
    const segmentCount = 15;
    const fishCount = window.innerWidth < 768 ? 8 : 15;
    const particles = [];
    const foodPellets = [];
    let nextFoodFeedId = 0;
    let width = 0;
    let height = 0;
    let pixelRatio = 1;
    let previousFrame = 0;

    function resizeCanvas() {
      width = window.innerWidth;
      height = window.innerHeight;
      pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = Math.round(width * pixelRatio);
      canvas.height = Math.round(height * pixelRatio);
      context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
    }

    window.addEventListener('resize', resizeCanvas);
    window.addEventListener('pointermove', event => {
      pointer.x = event.clientX;
      pointer.y = event.clientY;
    });
    window.addEventListener('pointerleave', () => {
      pointer.x = -1000;
      pointer.y = -1000;
    });
    window.addEventListener('click', event => {
      if (event.target.closest?.('.login-wrapper')) return;

      const pelletCount = 9;
      const feedId = ++nextFoodFeedId;
      for (let index = 0; index < pelletCount; index++) {
        const angle = (Math.PI * 2 * index) / pelletCount + Math.random() * 0.35;
        const spread = 8 + Math.random() * 34;
        foodPellets.push(new FoodPellet(
          event.clientX + Math.cos(angle) * spread,
          event.clientY + Math.sin(angle) * spread,
          feedId
        ));
      }
      if (foodPellets.length > 90) foodPellets.splice(0, foodPellets.length - 90);

      const activeFeedIds = new Set(foodPellets.filter(pellet => !pellet.eaten && pellet.life > 0).map(pellet => pellet.feedId));
      koiList.forEach(koi => {
        for (const oldFeedId of koi.foodMeals.keys()) {
          if (!activeFeedIds.has(oldFeedId)) koi.foodMeals.delete(oldFeedId);
        }
      });
    });

    class Particle {
      constructor() {
        this.x = Math.random() * width;
        this.y = Math.random() * height;
        this.size = 0.7 + Math.random() * 1.4;
        this.speed = 0.12 + Math.random() * 0.18;
        this.drift = (Math.random() - 0.5) * 0.12;
        this.opacity = 0.12 + Math.random() * 0.25;
      }

      update(frameScale) {
        this.x += this.drift * frameScale;
        this.y -= this.speed * frameScale;
        if (this.y < -5) {
          this.y = height + 5;
          this.x = Math.random() * width;
        }
        if (this.x < -5) this.x = width + 5;
        if (this.x > width + 5) this.x = -5;
      }

      draw() {
        context.fillStyle = `rgba(225, 244, 248, ${this.opacity})`;
        context.beginPath();
        context.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        context.fill();
      }
    }

    class FoodPellet {
      constructor(x, y, feedId) {
        this.x = x;
        this.y = y;
        this.feedId = feedId;
        this.radius = 2.4 + Math.random() * 1.8;
        this.phase = Math.random() * Math.PI * 2;
        this.life = 720;
        this.eaten = false;
      }

      update(frameScale) {
        this.life -= frameScale;
        this.phase += 0.045 * frameScale;
        this.y += Math.sin(this.phase) * 0.12 * frameScale;
      }

      draw() {
        const pulse = 1 + Math.sin(this.phase) * 0.12;
        context.save();
        context.shadowColor = 'rgba(255, 181, 60, .8)';
        context.shadowBlur = 9;
        context.fillStyle = '#ffc857';
        context.beginPath();
        context.arc(this.x, this.y, this.radius * pulse, 0, Math.PI * 2);
        context.fill();
        context.shadowBlur = 0;
        context.fillStyle = 'rgba(255, 244, 194, .85)';
        context.beginPath();
        context.arc(this.x - this.radius * .28, this.y - this.radius * .3, this.radius * .28, 0, Math.PI * 2);
        context.fill();
        context.restore();
      }
    }

    class Koi {
      constructor() {
        this.x = Math.random() * width;
        this.y = Math.random() * height;
        this.baseSpeed = 1.5 + Math.random() * 1.5;
        this.speed = this.baseSpeed;
        this.angle = Math.random() * Math.PI * 2;
        this.vx = Math.cos(this.angle) * this.speed;
        this.vy = Math.sin(this.angle) * this.speed;
        this.wanderAngle = this.angle;
        this.size = 0.45 + Math.random() * 1.05;
        this.swimCycle = Math.random() * 100;
        this.seed = Math.random() * 100;
        this.foodMeals = new Map();
        this.segments = Array.from({ length: segmentCount }, (_, index) => ({
          x: this.x - Math.cos(this.angle) * index * 2,
          y: this.y - Math.sin(this.angle) * index * 2
        }));
        const palettes = [
          { base: '#fff', patch: '#ef4444', accent: '#17212b', pattern: 'kohaku' },
          { base: '#fff', patch: '#dc2626', accent: '#17212b', pattern: 'sanke' },
          { base: '#202b36', patch: '#f8fafc', accent: '#e11d48', pattern: 'showa' },
          { base: '#fff', patch: '#dc2626', accent: '#fff', pattern: 'tancho' },
          { base: '#fbbf24', patch: '#fff4c2', accent: '#fff', pattern: 'ogon' },
          { base: '#f8fafc', patch: '#263746', accent: '#f97316', pattern: 'bekko' },
          { base: '#f97316', patch: '#fff', accent: '#17212b', pattern: 'reverse' }
        ];
        this.colors = palettes[Math.floor(Math.random() * palettes.length)];
      }

      update(frameScale) {
        this.wanderAngle += (Math.random() - 0.5) * 0.08 * frameScale;
        let forceX = Math.cos(this.wanderAngle) * 0.08 * frameScale;
        let forceY = Math.sin(this.wanderAngle) * 0.08 * frameScale;

        let foodTarget = null;
        let foodDistance = Infinity;
        const foodAttractionRadius = 160 + this.size * 80;
        for (const pellet of foodPellets) {
          if (pellet.eaten || pellet.life <= 0) continue;
          const distance = Math.hypot(pellet.x - this.x, pellet.y - this.y);
          if (distance > foodAttractionRadius) continue;
          let meal = this.foodMeals.get(pellet.feedId);
          if (!meal) {
            meal = { eaten: 0, quota: 1 + Math.floor(Math.random() * 2) };
            this.foodMeals.set(pellet.feedId, meal);
          }
          if (meal.eaten >= meal.quota) continue;
          if (distance <= foodAttractionRadius && distance < foodDistance) {
            foodDistance = distance;
            foodTarget = pellet;
          }
        }

        const dx = this.x - pointer.x;
        const dy = this.y - pointer.y;
        const distance = Math.hypot(dx, dy);
        const avoidanceRadius = 110;
        if (foodTarget && foodDistance > 0) {
          forceX += ((foodTarget.x - this.x) / foodDistance) * this.baseSpeed * 1.35 * frameScale;
          forceY += ((foodTarget.y - this.y) / foodDistance) * this.baseSpeed * 1.35 * frameScale;
          this.speed += (this.baseSpeed * 2.8 - this.speed) * 0.12 * frameScale;
        } else if (distance < avoidanceRadius) {
          const urgency = 1 - distance / avoidanceRadius;
          const escapeX = distance > 0 ? dx / distance : -Math.cos(this.angle);
          const escapeY = distance > 0 ? dy / distance : -Math.sin(this.angle);
          forceX += escapeX * urgency * this.baseSpeed * 2.5 * frameScale;
          forceY += escapeY * urgency * this.baseSpeed * 2.5 * frameScale;
          this.speed += (this.baseSpeed * 2.5 - this.speed) * 0.18 * frameScale;
        } else {
          this.speed += (this.baseSpeed - this.speed) * 0.025 * frameScale;
        }

        this.vx += forceX;
        this.vy += forceY;
        const currentSpeed = Math.hypot(this.vx, this.vy);
        if (currentSpeed > 0) {
          this.vx = (this.vx / currentSpeed) * this.speed;
          this.vy = (this.vy / currentSpeed) * this.speed;
        } else {
          this.vx = Math.cos(this.angle) * this.speed;
          this.vy = Math.sin(this.angle) * this.speed;
        }

        this.x += this.vx * frameScale;
        this.y += this.vy * frameScale;
        this.angle = Math.atan2(this.vy, this.vx);

        if (foodTarget && Math.hypot(foodTarget.x - this.x, foodTarget.y - this.y) < 13 + this.size * 2) {
          foodTarget.eaten = true;
          this.foodMeals.get(foodTarget.feedId).eaten++;
        }

        const buffer = 80;
        if (this.x < -buffer) this.x = width + buffer;
        if (this.x > width + buffer) this.x = -buffer;
        if (this.y < -buffer) this.y = height + buffer;
        if (this.y > height + buffer) this.y = -buffer;

        this.segments[0].x = this.x;
        this.segments[0].y = this.y;
        for (let index = 1; index < segmentCount; index++) {
          const previous = this.segments[index - 1];
          const segment = this.segments[index];
          const segmentAngle = Math.atan2(previous.y - segment.y, previous.x - segment.x);
          const segmentDistance = 4 * this.size;
          segment.x = previous.x - Math.cos(segmentAngle) * segmentDistance;
          segment.y = previous.y - Math.sin(segmentAngle) * segmentDistance;
        }
        this.swimCycle += (0.035 + this.speed * 0.025) * frameScale;
      }

      draw() {
        const renderedSegments = this.segments.map((segment, index) => {
          const previous = this.segments[Math.max(0, index - 1)];
          const next = this.segments[Math.min(segmentCount - 1, index + 1)];
          const angle = index === 0
            ? this.angle
            : Math.atan2(previous.y - next.y, previous.x - next.x);
          const wiggle = Math.sin(this.swimCycle - index * 0.4) * index * 0.4 * this.size;
          const radius = Math.sin((index / (segmentCount - 1)) * Math.PI) * 8.5 * this.size
            + (index === 0 ? 2.4 * this.size : 0);
          return {
            x: segment.x + Math.cos(angle + Math.PI / 2) * wiggle,
            y: segment.y + Math.sin(angle + Math.PI / 2) * wiggle,
            angle,
            radius
          };
        });
        context.save();
        context.lineCap = 'round';
        context.lineJoin = 'round';

        const fin = renderedSegments[3];
        const finLength = 17 * this.size;
        context.fillStyle = this.colors.base;
        context.globalAlpha = 0.48;
        for (const side of [-1, 1]) {
          const finAngle = fin.angle + side * (Math.PI / 2.5);
          context.beginPath();
          context.moveTo(fin.x, fin.y);
          context.quadraticCurveTo(
            fin.x + Math.cos(finAngle) * finLength,
            fin.y + Math.sin(finAngle) * finLength,
            fin.x + Math.cos(fin.angle + side * Math.PI / 3) * finLength * 0.55,
            fin.y + Math.sin(fin.angle + side * Math.PI / 3) * finLength * 0.55
          );
          context.fill();
        }
        context.globalAlpha = 1;

        context.beginPath();
        renderedSegments.forEach((segment, index) => {
          const x = segment.x + Math.cos(segment.angle + Math.PI / 2) * segment.radius;
          const y = segment.y + Math.sin(segment.angle + Math.PI / 2) * segment.radius;
          if (index === 0) context.moveTo(x, y);
          else context.lineTo(x, y);
        });
        for (let index = segmentCount - 1; index >= 0; index--) {
          const segment = renderedSegments[index];
          context.lineTo(
            segment.x + Math.cos(segment.angle - Math.PI / 2) * segment.radius,
            segment.y + Math.sin(segment.angle - Math.PI / 2) * segment.radius
          );
        }
        context.closePath();

        context.shadowColor = 'rgba(0, 0, 0, .38)';
        context.shadowBlur = 9 * this.size;
        context.shadowOffsetY = 5 * this.size;
        context.fillStyle = this.colors.base;
        context.fill();
        context.shadowColor = 'transparent';
        context.shadowBlur = 0;
        context.shadowOffsetY = 0;

        context.save();
        context.clip();
        if (this.colors.pattern === 'tancho') {
          const headMark = renderedSegments[2];
          context.fillStyle = this.colors.patch;
          context.beginPath();
          context.arc(headMark.x, headMark.y, headMark.radius * 0.9, 0, Math.PI * 2);
          context.fill();
        } else if (this.colors.pattern === 'ogon') {
          context.strokeStyle = 'rgba(255, 255, 255, .42)';
          context.lineWidth = 0.65 * this.size;
          for (let index = 3; index < segmentCount - 2; index += 2) {
            const segment = renderedSegments[index];
            const sideX = Math.cos(segment.angle + Math.PI / 2) * segment.radius * 0.62;
            const sideY = Math.sin(segment.angle + Math.PI / 2) * segment.radius * 0.62;
            context.beginPath();
            context.moveTo(segment.x - sideX, segment.y - sideY);
            context.lineTo(segment.x + sideX, segment.y + sideY);
            context.stroke();
          }
        } else {
          for (let index = 2; index < segmentCount - 2; index++) {
            const segment = renderedSegments[index];
            const isBandPattern = this.colors.pattern === 'kohaku' || this.colors.pattern === 'reverse';
            const hasMark = isBandPattern
              ? Math.sin(index * 1.45 + this.seed) > 0.05
              : Math.sin(index * 1.2 + this.seed) > 0.32;

            if (hasMark) {
              context.fillStyle = this.colors.patch;
              context.beginPath();
              context.ellipse(
                segment.x,
                segment.y,
                segment.radius * (isBandPattern ? 1.7 : 1.2),
                segment.radius * 0.9,
                segment.angle,
                0,
                Math.PI * 2
              );
              context.fill();
            }

            if ((this.colors.pattern === 'sanke' || this.colors.pattern === 'showa')
              && Math.cos(index * 2.1 + this.seed) > 0.72) {
              context.fillStyle = this.colors.accent;
              context.beginPath();
              context.arc(
                segment.x + Math.cos(segment.angle + Math.PI / 2) * segment.radius * 0.25,
                segment.y + Math.sin(segment.angle + Math.PI / 2) * segment.radius * 0.25,
                Math.max(0.8, this.size * 0.8),
                0,
                Math.PI * 2
              );
              context.fill();
            }
          }
        }
        context.restore();

        const tail = renderedSegments[segmentCount - 1];
        const tailPrevious = renderedSegments[segmentCount - 2];
        const tailAngle = Math.atan2(tail.y - tailPrevious.y, tail.x - tailPrevious.x);
        const tailLength = 22 * this.size;
        const tailWiggle = Math.sin(this.swimCycle - segmentCount * .4) * .1;
        const tailSpread = Math.PI / 4;
        context.save();
        context.translate(tail.x, tail.y);
        context.rotate(tailAngle + tailWiggle);
        context.globalAlpha = .82;
        context.fillStyle = this.colors.base;
        context.beginPath();
        context.moveTo(0, 0);
        context.quadraticCurveTo(
          Math.cos(-tailSpread) * tailLength,
          Math.sin(-tailSpread) * tailLength,
          tailLength * 1.2,
          0
        );
        context.quadraticCurveTo(
          Math.cos(tailSpread) * tailLength,
          Math.sin(tailSpread) * tailLength,
          0,
          0
        );
        context.fill();
        context.restore();

        const head = renderedSegments[0];
        context.fillStyle = '#14232b';
        for (const side of [-1, 1]) {
          context.beginPath();
          context.arc(
            head.x + Math.cos(head.angle + side * Math.PI / 2.5) * 2.5 * this.size,
            head.y + Math.sin(head.angle + side * Math.PI / 2.5) * 2.5 * this.size,
            1.15 * this.size,
            0,
            Math.PI * 2
          );
          context.fill();
        }
        context.save();
        context.restore();
      }
    }

    resizeCanvas();
    const koiList = Array.from({ length: fishCount }, () => new Koi());
    const particleCount = window.innerWidth < 768 ? 22 : 38;
    for (let index = 0; index < particleCount; index++) particles.push(new Particle());

    function animate(timestamp = 0) {
      const frameScale = previousFrame ? Math.min((timestamp - previousFrame) / 16.67, 2) : 1;
      previousFrame = timestamp;
      context.clearRect(0, 0, width, height);

      particles.forEach(particle => {
        if (!reducedMotion.matches) particle.update(frameScale);
        particle.draw();
      });

      for (let index = foodPellets.length - 1; index >= 0; index--) {
        const pellet = foodPellets[index];
        if (!reducedMotion.matches) pellet.update(frameScale);
        if (pellet.eaten || pellet.life <= 0) {
          foodPellets.splice(index, 1);
        } else {
          pellet.draw();
        }
      }

      koiList.sort((first, second) => first.size - second.size);
      koiList.forEach(koi => {
        if (!reducedMotion.matches) koi.update(frameScale);
        koi.draw();
      });
      if (!reducedMotion.matches) window.requestAnimationFrame(animate);
    }

    animate();
  </script>
</body>
</html>