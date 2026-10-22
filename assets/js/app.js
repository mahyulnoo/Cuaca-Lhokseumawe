/**
 * Lhokseumawe Weather Frontend Logic
 * Integrasi API BMKG & Data Handler
 */

const App = {
    currentDistrict: 'banda_sakti',
    weatherData: null,
    
    init() {
        this.bindEvents();
        this.loadWeather(this.currentDistrict);
        this.startClock();
    },

    bindEvents() {
        // District buttons click
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

        // Refresh button
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
            const response = await fetch(`api/weather.php?district=${districtKey}`);
            if (!response.ok) {
                throw new Error('Gagal menghubungi API');
            }
            const result = await response.json();
            if (result.status === 'success') {
                this.weatherData = result;
                this.render(result);
            } else {
                alert(result.message || 'Gagal memuat cuaca.');
            }
        } catch (err) {
            console.error('Fetch error:', err);
            const curDesc = document.getElementById('curDesc');
            const curDescEn = document.getElementById('curDescEn');
            if (curDesc) curDesc.textContent = 'Server BMKG Sibuk';
            if (curDescEn) curDescEn.textContent = 'Klik tombol Refresh untuk mencoba kembali';
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

        // Cari slot yang paling dekat dengan jam saat ini
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

        // Update Theme Day vs Night berdasarkan jam cuaca
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

        // 5. Render Hourly Forecast (Ambil 8-10 slot pertama terhitung dari hari ini)
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

                // Hitung suhu min, suhu max, dan cuaca siang/dominan
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

                // Ambil slot siang sekitar jam 11:00 / 14:00 sebagai ikon representatif
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

// Inisialisasi saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    App.init();
});
