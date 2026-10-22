<?php
/**
 * Portal Informasi Cuaca Kota Lhokseumawe
 * Integrasi Resmi API Publik BMKG (Badan Meteorologi, Klimatologi, dan Geofisika)
 */

$districts = [
    'banda_sakti' => [
        'name' => 'Banda Sakti',
        'sub' => 'Kuta Blang / Pusat Kota',
        'adm4' => '11.73.02.2001'
    ],
    'muara_dua' => [
        'name' => 'Muara Dua',
        'sub' => 'Alue Awe / Cunda',
        'adm4' => '11.73.01.2002'
    ],
    'muara_satu' => [
        'name' => 'Muara Satu',
        'sub' => 'Batuphat / Blang Panyang',
        'adm4' => '11.73.04.2001'
    ],
    'blang_mangat' => [
        'name' => 'Blang Mangat',
        'sub' => 'Kuala / Punteuet',
        'adm4' => '11.73.03.2001'
    ]
];

$activeDistrict = 'banda_sakti';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuaca Lhokseumawe - Prakiraan Cuaca Terkini BMKG</title>
    <meta name="description" content="Informasi prakiraan cuaca real-time dan 3 hari ke depan untuk seluruh kecamatan di Kota Lhokseumawe, Aceh langsung dari API resmi BMKG.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="https://api-apps.bmkg.go.id/storage/icon/cuaca/cerah-am.svg">
</head>
<body class="theme-day">
    <div class="bg-glow"></div>
    <div class="bg-glow-2"></div>

    <div class="container">
        <!-- Header -->
        <header class="app-header">
            <div class="brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <h1>Cuaca Lhokseumawe</h1>
                    <p>Prakiraan Cuaca Resmi BMKG • Kota Lhokseumawe, Aceh</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="badge-bmkg">
                    <span class="badge-dot"></span>
                    <span>API BMKG Aktif</span>
                </div>
                <button class="btn-refresh" id="btnRefresh" title="Muat Ulang Data Cuaca">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    <span>Refresh</span>
                </button>
            </div>
        </header>

        <!-- District Selector -->
        <nav class="district-selector-bar" aria-label="Pilih Kecamatan">
            <?php foreach ($districts as $key => $dist): ?>
                <button type="button" class="district-chip <?= $key === $activeDistrict ? 'active' : '' ?>" data-district="<?= $key ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span><?= htmlspecialchars($dist['name']) ?></span>
                </button>
            <?php endforeach; ?>
        </nav>

        <!-- Hero Section: Current Weather & Metrics -->
        <main>
            <section class="weather-hero">
                <!-- Current Main Card -->
                <div class="glass-card main-card">
                    <div class="location-meta">
                        <div>
                            <h2 class="loc-title" id="locTitle">Kecamatan Banda Sakti</h2>
                            <div class="loc-sub" id="locSub">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                <span>Pusat Kota • Lhokseumawe</span>
                            </div>
                        </div>
                        <div class="current-time" id="liveClock">--:--:-- WIB</div>
                    </div>

                    <div class="weather-center">
                        <div class="temp-large-box">
                            <span class="temp-val" id="curTemp">--</span>
                            <span class="temp-unit">°C</span>
                        </div>
                        <div class="condition-box">
                            <img src="https://api-apps.bmkg.go.id/storage/icon/cuaca/cerah-am.svg" alt="Cuaca" id="curIcon" class="condition-icon">
                            <div>
                                <div class="condition-desc" id="curDesc">Memuat Cuaca...</div>
                                <div class="condition-desc-en" id="curDescEn">Please wait...</div>
                            </div>
                        </div>
                    </div>

                    <div class="main-card-footer">
                        <div class="cache-indicator">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span id="cacheIndicator">Sinkronisasi Data BMKG...</span>
                        </div>
                        <div>
                            <span>Zona Waktu: <strong>WIB (UTC+7)</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="metrics-grid">
                    <!-- Humidity -->
                    <div class="glass-card metric-card">
                        <div class="metric-icon-wrap" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                            </svg>
                        </div>
                        <div class="metric-info">
                            <div class="m-label">Kelembapan Udara</div>
                            <div class="m-val" id="mHumidity">--%</div>
                            <div class="m-sub">Relatif (RH)</div>
                        </div>
                    </div>

                    <!-- Wind Speed -->
                    <div class="glass-card metric-card">
                        <div class="metric-icon-wrap" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"></path>
                            </svg>
                        </div>
                        <div class="metric-info">
                            <div class="m-label">Kecepatan Angin</div>
                            <div class="m-val" id="mWind">-- km/j</div>
                            <div class="m-sub" id="mWindSub">Arah angin terkini</div>
                        </div>
                    </div>

                    <!-- Cloud Cover -->
                    <div class="glass-card metric-card">
                        <div class="metric-icon-wrap" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path>
                            </svg>
                        </div>
                        <div class="metric-info">
                            <div class="m-label">Tutupan Awan</div>
                            <div class="m-val" id="mCloud">--%</div>
                            <div class="m-sub">Persentase langit</div>
                        </div>
                    </div>

                    <!-- Rain Precipitation -->
                    <div class="glass-card metric-card">
                        <div class="metric-icon-wrap" style="background: rgba(139, 92, 246, 0.15); color: #a855f7;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 16.58A5 5 0 0 0 18 7h-1.26A8 8 0 1 0 4 15.25"></path>
                                <path d="M8 19v2"></path>
                                <path d="M8 13v2"></path>
                                <path d="M12 21v2"></path>
                                <path d="M12 15v2"></path>
                                <path d="M16 19v2"></path>
                                <path d="M16 13v2"></path>
                            </svg>
                        </div>
                        <div class="metric-info">
                            <div class="m-label">Potensi Presipitasi</div>
                            <div class="m-val" id="mRain">0.0 mm</div>
                            <div class="m-sub">Curah Hujan</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Hourly Forecast Section -->
            <section>
                <div class="section-header">
                    <h2 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-cyan">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Prakiraan Rentang Waktu (Per 3 Jam)</span>
                    </h2>
                    <span style="font-size: 0.8rem; color: var(--text-subtle);">Geser horizontal &rarr;</span>
                </div>
                <div class="hourly-container" id="hourlyList">
                    <!-- Hourly cards diisi dinamis oleh app.js -->
                    <div class="hourly-card skeleton" style="height: 155px;"></div>
                    <div class="hourly-card skeleton" style="height: 155px;"></div>
                    <div class="hourly-card skeleton" style="height: 155px;"></div>
                    <div class="hourly-card skeleton" style="height: 155px;"></div>
                    <div class="hourly-card skeleton" style="height: 155px;"></div>
                </div>
            </section>

            <!-- 3-Day Forecast Section -->
            <section>
                <div class="section-header">
                    <h2 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Prakiraan Cuaca 3 Hari ke Depan</span>
                    </h2>
                </div>
                <div class="daily-grid" id="dailyGrid">
                    <!-- 3 Daily cards diisi dinamis oleh app.js -->
                    <div class="glass-card skeleton" style="height: 170px;"></div>
                    <div class="glass-card skeleton" style="height: 170px;"></div>
                    <div class="glass-card skeleton" style="height: 170px;"></div>
                </div>
            </section>

            <!-- Info Section -->
            <section class="info-section">
                <div class="info-content">
                    <h3>Tentang Cuaca Kota Lhokseumawe</h3>
                    <p>
                        Kota Lhokseumawe terletak di pesisir timur Provinsi Aceh dengan iklim tropis bahari. Data cuaca diperbarui secara berkala dari stasiun pengamatan dan radar <strong>Badan Meteorologi, Klimatologi, dan Geofisika (BMKG)</strong> Republik Indonesia melalui antarmuka data terbuka.
                    </p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">Stasiun Referensi:</div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #ffffff;">Stasiun Meteorologi Malikussaleh</div>
                    <div style="font-size: 0.775rem; color: var(--accent-cyan);">Aceh Utara / Lhokseumawe</div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="app-footer">
            <p>Dikembangkan dengan PHP & Native Web Technologies • Data bersumber dari <a href="https://api.bmkg.go.id" target="_blank" rel="noopener">Open Data API BMKG</a></p>
            <p style="margin-top: 0.35rem; font-size: 0.78rem;">Pusat Meteorologi Publik BMKG • Khusus Wilayah Kota Lhokseumawe, Aceh</p>
        </footer>
    </div>

    <script>
    const App = {
        currentDistrict: 'banda_sakti',
        weatherData: null,
        
        init() {
            this.bindEvents();
            this.loadWeather(this.currentDistrict);
            this.startClock();
        },

        bindEvents() {
            const districtButtons = document.querySelectorAll('.district-chip');
            districtButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const targetKey = btn.getAttribute('data-district');
                    if (targetKey && targetKey !== this.currentDistrict) {
                        districtButtons.forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        this.currentDistrict = targetKey;
                        this.loadWeather(targetKey);
                    }
                });
            });

            const refreshBtn = document.getElementById('btnRefresh');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', () => {
                    refreshBtn.classList.add('spinning');
                    this.loadWeather(this.currentDistrict, () => {
                        setTimeout(() => refreshBtn.classList.remove('spinning'), 600);
                    });
                });
            }
        },

        startClock() {
            const updateClock = () => {
                const clockEl = document.getElementById('liveClock');
                if (!clockEl) return;
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;
            };
            updateClock();
            setInterval(updateClock, 1000);
        },

        async loadWeather(districtKey, callback = null) {
            try {
                // Gunakan path relatif sederhana yang selalu valid baik di localhost maupun Vercel
                const apiUrl = `api/weather.php?district=${districtKey}&t=${Date.now()}`;
                console.log('Fetching:', apiUrl);

                const response = await fetch(apiUrl);
                const rawText = await response.text();
                let result;
                try {
                    result = JSON.parse(rawText);
                } catch(e) {
                    throw new Error('Respon bukan JSON: ' + rawText.replace(/<[^>]*>?/gm, '').trim().substring(0, 100));
                }
                console.log('API Result:', result);

                if (result.status === 'success' && result.raw) {
                    this.weatherData = result;
                    this.render(result);
                } else {
                    throw new Error(result.message || 'Format data cuaca tidak valid');
                }
            } catch (err) {
                console.error('Weather load error:', err);
                const curDesc = document.getElementById('curDesc');
                const curDescEn = document.getElementById('curDescEn');
                if (curDesc) curDesc.textContent = 'Server BMKG Sibuk';
                if (curDescEn) curDescEn.textContent = (err.message || 'Klik tombol Refresh untuk mencoba kembali');
            } finally {
                if (typeof callback === 'function') callback();
            }
        },

        render(data) {
            const raw = data.raw;
            const lokasi = raw.lokasi || {};
            const districtInfo = data.district_info || {};
            const cuacaDays = raw.data && raw.data[0] ? raw.data[0].cuaca : [];

            // 1. Update Header / Location Names
            const locTitle = document.getElementById('locTitle');
            const locSub = document.getElementById('locSub');
            const cacheIndicator = document.getElementById('cacheIndicator');

            if (locTitle) {
                locTitle.innerHTML = `Kecamatan ${districtInfo.name || lokasi.kecamatan || 'Lhokseumawe'}`;
            }
            if (locSub) {
                locSub.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg> Gampong ${lokasi.desa || districtInfo.sub} • Kota Lhokseumawe`;
            }
            if (cacheIndicator) {
                cacheIndicator.textContent = data.cached ? `Cache BMKG (${data.cache_time.slice(11, 16)} WIB)` : `Live BMKG (${data.cache_time.slice(11, 16)} WIB)`;
            }

            // 2. Tentukan prakiraan cuaca yang paling relevan dengan waktu sekarang
            const flatForecasts = [];
            cuacaDays.forEach(day => {
                if (Array.isArray(day)) {
                    day.forEach(slot => flatForecasts.push(slot));
                }
            });

            if (flatForecasts.length === 0) return;

            const now = new Date();
            let closestSlot = flatForecasts[0];
            let minDiff = Infinity;

            flatForecasts.forEach(slot => {
                const slotDate = new Date(slot.local_datetime.replace(' ', 'T'));
                const diff = Math.abs(slotDate - now);
                if (diff < minDiff) {
                    minDiff = diff;
                    closestSlot = slot;
                }
            });

            // Update Theme Day vs Night
            const hour = new Date(closestSlot.local_datetime.replace(' ', 'T')).getHours();
            if (hour >= 6 && hour < 18) {
                document.body.classList.remove('theme-night');
                document.body.classList.add('theme-day');
            } else {
                document.body.classList.remove('theme-day');
                document.body.classList.add('theme-night');
            }

            // 3. Render Kartu Cuaca Utama
            const curTemp = document.getElementById('curTemp');
            const curIcon = document.getElementById('curIcon');
            const curDesc = document.getElementById('curDesc');
            const curDescEn = document.getElementById('curDescEn');

            if (curTemp) curTemp.textContent = closestSlot.t;
            if (curIcon) {
                curIcon.src = closestSlot.image;
                curIcon.alt = closestSlot.weather_desc;
            }
            if (curDesc) curDesc.textContent = closestSlot.weather_desc;
            if (curDescEn) curDescEn.textContent = closestSlot.weather_desc_en;

            // 4. Render Metrics
            const mHumidity = document.getElementById('mHumidity');
            const mWind = document.getElementById('mWind');
            const mCloud = document.getElementById('mCloud');
            const mRain = document.getElementById('mRain');

            if (mHumidity) mHumidity.textContent = `${closestSlot.hu}%`;
            if (mWind) {
                mWind.textContent = `${closestSlot.ws} km/j`;
                const mWindSub = document.getElementById('mWindSub');
                if (mWindSub) mWindSub.textContent = `Arah dari ${closestSlot.wd} (${closestSlot.wd_deg}°) ke ${closestSlot.wd_to}`;
            }
            if (mCloud) mCloud.textContent = `${closestSlot.tcc}%`;
            if (mRain) {
                const precip = closestSlot.tp !== undefined && closestSlot.tp !== null ? closestSlot.tp : 0;
                mRain.textContent = `${precip} mm`;
            }

            // 5. Render Hourly Forecast
            const hourlyList = document.getElementById('hourlyList');
            if (hourlyList) {
                hourlyList.innerHTML = '';
                const hourlySlots = flatForecasts.slice(0, 10);
                hourlySlots.forEach((slot, idx) => {
                    const dateObj = new Date(slot.local_datetime.replace(' ', 'T'));
                    const timeStr = String(dateObj.getHours()).padStart(2, '0') + ':00';
                    const isClosest = (slot === closestSlot);

                    const card = document.createElement('div');
                    card.className = `hourly-card ${isClosest ? 'active-hour' : ''}`;
                    card.innerHTML = `
                        <div class="hourly-time">${timeStr} ${isClosest ? '• Sekarang' : ''}</div>
                        <img src="${slot.image}" alt="${slot.weather_desc}" class="hourly-icon" loading="lazy">
                        <div class="hourly-temp">${slot.t}°</div>
                        <div class="hourly-desc" title="${slot.weather_desc}">${slot.weather_desc}</div>
                        <div class="hourly-extra">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                            <span>${slot.hu}%</span>
                        </div>
                    `;
                    hourlyList.appendChild(card);
                });
            }

            // 6. Render 3-Day Forecast Summary
            const dailyGrid = document.getElementById('dailyGrid');
            if (dailyGrid && Array.isArray(cuacaDays)) {
                dailyGrid.innerHTML = '';
                
                const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

                cuacaDays.forEach((daySlots, dayIndex) => {
                    if (!Array.isArray(daySlots) || daySlots.length === 0) return;

                    let minT = Infinity;
                    let maxT = -Infinity;
                    let avgHu = 0;
                    let totalWs = 0;

                    daySlots.forEach(s => {
                        if (s.t < minT) minT = s.t;
                        if (s.t > maxT) maxT = s.t;
                        avgHu += s.hu;
                        totalWs += s.ws;
                    });
                    avgHu = Math.round(avgHu / daySlots.length);
                    const avgWs = (totalWs / daySlots.length).toFixed(1);

                    let repSlot = daySlots.find(s => {
                        const h = new Date(s.local_datetime.replace(' ', 'T')).getHours();
                        return h >= 11 && h <= 14;
                    }) || daySlots[0];

                    const firstDate = new Date(daySlots[0].local_datetime.replace(' ', 'T'));
                    let labelDay = dayNames[firstDate.getDay()];
                    if (dayIndex === 0) labelDay = 'Hari Ini';
                    if (dayIndex === 1) labelDay = 'Besok';

                    const dateFormatted = `${firstDate.getDate()} ${monthNames[firstDate.getMonth()]}`;

                    const dayCard = document.createElement('div');
                    dayCard.className = 'glass-card daily-card';
                    dayCard.innerHTML = `
                        <div class="daily-head">
                            <span class="daily-day">${labelDay}</span>
                            <span class="daily-date">${dateFormatted}</span>
                        </div>
                        <div class="daily-body">
                            <img src="${repSlot.image}" alt="${repSlot.weather_desc}" class="daily-icon" loading="lazy">
                            <div class="daily-weather-info">
                                <div class="d-desc">${repSlot.weather_desc}</div>
                                <div class="daily-temp-range">
                                    <span class="daily-temp-max">${maxT}°</span>
                                    <span class="daily-temp-min">/ ${minT}°C</span>
                                </div>
                            </div>
                        </div>
                        <div class="daily-stats">
                            <div><span class="text-muted">Kelembapan:</span> ${avgHu}%</div>
                            <div><span class="text-muted">Angin:</span> ~${avgWs} km/j</div>
                        </div>
                    `;
                    dailyGrid.appendChild(dayCard);
                });
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        App.init();
    });
    </script>
</body>
</html>
