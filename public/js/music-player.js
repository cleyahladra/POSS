/**
 * POSS Music Player
 * Floating background music widget for the POS System dashboard.
 * - Persists state across page navigation (localStorage)
 * - Auto-stops on logout
 * - 5 moods × 3 tracks each (royalty-free via CDN)
 */

(function () {
    'use strict';

    /* ── TRACK LIBRARY ─────────────────────────────────────────────────
       Using Pixabay / Bensound royalty-free streams.
       Replace src with your own hosted files if needed.
    ──────────────────────────────────────────────────────────────────── */
    const MOODS = {
        chill: {
            label: 'Chill',
            icon: '🌊',
            color: '#4f8ef7',
            tracks: [
                { title: 'Ocean Drift', src: '/audio/chill-1.mp3' },
                { title: 'Soft Skies', src: '/audio/chill-2.mp3' },
                { title: 'Calm Horizon', src: '/audio/chill-3.mp3' },
            ],
        },
        focus: {
            label: 'Focus',
            icon: '🎯',
            color: '#00d4aa',
            tracks: [
                { title: 'Deep Work', src: '/audio/focus-1.mp3' },
                { title: 'Clarity Flow', src: '/audio/focus-2.mp3' },
                { title: 'Precision Mode', src: '/audio/focus-3.mp3' },
            ],
        },
        energetic: {
            label: 'Energetic',
            icon: '⚡',
            color: '#f59e0b',
            tracks: [
                { title: 'Drive Forward', src: '/audio/energetic-1.mp3' },
                { title: 'Pulse Rush', src: '/audio/energetic-2.mp3' },
                { title: 'High Voltage', src: '/audio/energetic-3.mp3' },
            ],
        },
        relaxing: {
            label: 'Relaxing',
            icon: '🍃',
            color: '#a78bfa',
            tracks: [
                { title: 'Gentle Rain', src: '/audio/relaxing-1.mp3' },
                { title: 'Quiet Valley', src: '/audio/-2.mp3' },
                { title: 'Still Water', src: '/audio/relaxing-3.mp3' },
            ],
        },
        lofi: {
            label: 'Lo-fi',
            icon: '🎵',
            color: '#f472b6',
            tracks: [
                { title: 'Late Night Study', src: '/audio/lofi-1.mp3' },
                { title: 'Vinyl Groove', src: '/audio/lofi-2.mp3' },
                { title: 'Cafe Owner', src: '/audio/lofi-3.mp3' },
            ],
        },
    };

    const MOOD_KEYS = Object.keys(MOODS);
    const MOOD_EMOJIS = { chill:'🌊', focus:'🎯', energetic:'⚡', relaxing:'🍃', lofi:'🎵' };

    /* ── STATE ────────────────────────────────────────────────────────── */
    const state = {
        mood:       localStorage.getItem('mp_mood')    || 'focus',
        trackIdx:   parseInt(localStorage.getItem('mp_track') || '0', 10),
        volume:     parseFloat(localStorage.getItem('mp_vol')  || '0.6'),
        muted:      localStorage.getItem('mp_muted')   === 'true',
        playing:    false,
        open:       false,
        listOpen:   false,
    };

    /* ── AUDIO ────────────────────────────────────────────────────────── */
    const audio = new Audio();
    audio.loop = false;
    audio.volume = state.muted ? 0 : state.volume;
    audio.preload = 'auto';
    audio.crossOrigin = 'anonymous';

    function currentTrack() {
        const m = MOODS[state.mood];
        const idx = Math.min(state.trackIdx, m.tracks.length - 1);
        return m.tracks[idx];
    }

    function loadTrack(animate) {
        const t = currentTrack();
        audio.src = t.src;
        audio.load();
        if (animate) {
            const nameEl = document.querySelector('.mp-track-name');
            if (nameEl) {
                nameEl.classList.remove('changing');
                void nameEl.offsetWidth;
                nameEl.classList.add('changing');
            }
        }
        updateTrackUI();
        saveState();
    }

    function saveState() {
        localStorage.setItem('mp_mood',  state.mood);
        localStorage.setItem('mp_track', state.trackIdx);
        localStorage.setItem('mp_vol',   state.volume);
        localStorage.setItem('mp_muted', state.muted);
    }

    /* ── PLAYBACK ─────────────────────────────────────────────────────── */
    function play() {
        audio.play().then(() => {
            state.playing = true;
            updatePlayUI();
        }).catch(() => {
            // Autoplay blocked — user must interact first
        });
    }

    function pause() {
        audio.pause();
        state.playing = false;
        updatePlayUI();
    }

    function togglePlay() {
        if (state.playing) pause(); else play();
    }

    function nextTrack() {
        const len = MOODS[state.mood].tracks.length;
        state.trackIdx = (state.trackIdx + 1) % len;
        loadTrack(true);
        if (state.playing) play();
    }

    function prevTrack() {
        const len = MOODS[state.mood].tracks.length;
        state.trackIdx = (state.trackIdx - 1 + len) % len;
        loadTrack(true);
        if (state.playing) play();
    }

    function setMood(key) {
        state.mood = key;
        state.trackIdx = 0;
        loadTrack(true);
        if (state.playing) play();
        updateMoodUI();
    }

    function setTrack(idx) {
        state.trackIdx = idx;
        loadTrack(true);
        if (state.playing) play();
    }

    /* ── AUDIO EVENTS ─────────────────────────────────────────────────── */
    audio.addEventListener('ended', nextTrack);
    audio.addEventListener('timeupdate', updateProgress);
    audio.addEventListener('error', nextTrack);

    function updateProgress() {
        const fill  = document.querySelector('.mp-progress-fill');
        const cur   = document.getElementById('mp-time-cur');
        const total = document.getElementById('mp-time-tot');
        if (!fill) return;
        const pct = audio.duration ? (audio.currentTime / audio.duration) * 100 : 0;
        fill.style.width = pct + '%';
        if (cur)   cur.textContent   = fmt(audio.currentTime);
        if (total) total.textContent = fmt(audio.duration || 0);
    }

    function fmt(s) {
        const m = Math.floor(s / 60);
        const sec = Math.floor(s % 60);
        return m + ':' + (sec < 10 ? '0' : '') + sec;
    }

    /* ── UI UPDATES ───────────────────────────────────────────────────── */
    function updatePlayUI() {
        const playBtn  = document.getElementById('mp-play-btn');
        const bubble   = document.getElementById('music-bubble');
        const art      = document.querySelector('.mp-art');
        if (playBtn) playBtn.innerHTML = state.playing
            ? '<i class="fas fa-pause"></i>'
            : '<i class="fas fa-play"></i>';
        if (bubble) bubble.classList.toggle('playing', state.playing);
        if (art)    art.classList.toggle('spinning', state.playing);
        animateVisualizer(state.playing);
    }

    function updateTrackUI() {
        const t    = currentTrack();
        const mood = MOODS[state.mood];
        const nameEl = document.querySelector('.mp-track-name');
        const moodEl = document.querySelector('.mp-track-mood');
        const numEl  = document.querySelector('.mp-track-num');
        const artEl  = document.querySelector('.mp-art');
        if (nameEl) nameEl.textContent = t.title;
        if (moodEl) moodEl.textContent = mood.icon + ' ' + mood.label;
        if (numEl)  numEl.textContent  = (state.trackIdx + 1) + ' / ' + mood.tracks.length;
        if (artEl)  artEl.style.setProperty('--art-color', mood.color);
        // track list
        document.querySelectorAll('.mp-track-item').forEach((el, i) => {
            el.classList.toggle('active', i === state.trackIdx);
        });
    }

    function updateMoodUI() {
        document.querySelectorAll('.mp-mood-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.mood === state.mood);
        });
        // rebuild track list
        rebuildTrackList();
        updateTrackUI();
    }

    function updateVolumeUI() {
        const slider = document.getElementById('mp-vol-slider');
        const pctEl  = document.getElementById('mp-vol-pct');
        const icon   = document.getElementById('mp-mute-btn');
        if (slider) slider.value = state.volume;
        if (pctEl)  pctEl.textContent = Math.round(state.volume * 100) + '%';
        if (icon) {
            icon.classList.toggle('muted', state.muted);
            if (state.muted)           icon.innerHTML = '<i class="fas fa-volume-mute"></i>';
            else if (state.volume < .3) icon.innerHTML = '<i class="fas fa-volume-off"></i>';
            else if (state.volume < .7) icon.innerHTML = '<i class="fas fa-volume-down"></i>';
            else                        icon.innerHTML = '<i class="fas fa-volume-up"></i>';
        }
        updateVolSliderBg();
    }

    function updateVolSliderBg() {
        const slider = document.getElementById('mp-vol-slider');
        if (!slider) return;
        const pct = state.muted ? 0 : state.volume * 100;
        slider.style.background = `linear-gradient(to right, var(--teal) ${pct}%, var(--bg4) ${pct}%)`;
    }

    /* ── VISUALIZER ───────────────────────────────────────────────────── */
    function animateVisualizer(active) {
        document.querySelectorAll('.mp-vis-bar').forEach((bar, i) => {
            bar.classList.toggle('active', active);
            if (active) {
                bar.style.setProperty('--dur', (0.4 + Math.random() * 0.5).toFixed(2) + 's');
            }
        });
    }

    /* ── BUILD HTML ───────────────────────────────────────────────────── */
    function buildTrackListItems() {
        return MOODS[state.mood].tracks.map((t, i) => `
      <div class="mp-track-item ${i === state.trackIdx ? 'active' : ''}" data-idx="${i}">
        <span class="mp-track-item-num">${i + 1}</span>
        <span class="mp-track-item-name">${t.title}</span>
        <span class="mp-track-item-playing">▶ NOW</span>
      </div>`).join('');
    }

    function buildMoodTabs() {
        return MOOD_KEYS.map(k => `
      <button class="mp-mood-btn ${k === state.mood ? 'active' : ''}" data-mood="${k}">
        <span>${MOODS[k].icon}</span>${MOODS[k].label}
      </button>`).join('');
    }

    function buildVisBars() {
        return Array.from({ length: 18 }, () =>
            `<div class="mp-vis-bar" style="height: ${Math.random()*12+3}px"></div>`
        ).join('');
    }

    function buildPlayer() {
        const t    = currentTrack();
        const mood = MOODS[state.mood];

        const html = `
    <!-- Floating toggle bubble -->
    <div id="music-bubble" title="Music Player">
      <i class="fas fa-music bubble-icon"></i>
      <div class="bubble-eq">
        <span></span><span></span><span></span><span></span>
      </div>
    </div>

    <!-- Player panel -->
    <div id="music-player" role="region" aria-label="Background Music Player">

      <!-- Header -->
      <div class="mp-header">
        <div class="mp-header-left">
          <div class="mp-logo"><i class="fas fa-music"></i></div>
          <span class="mp-title">Ambient Music</span>
        </div>
        <button class="mp-close" id="mp-close-btn" title="Close player" aria-label="Close music player">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Mood tabs -->
      <div class="mp-moods" role="tablist" aria-label="Mood categories">
        ${buildMoodTabs()}
      </div>

      <!-- Now playing -->
      <div class="mp-now-playing">
        <div class="mp-track-info">
          <div class="mp-art" style="--art-color:${mood.color}">
            <i class="fas fa-compact-disc" style="color:var(--art-color)"></i>
            <div class="mp-art-ring"></div>
          </div>
          <div class="mp-track-meta">
            <div class="mp-track-name">${t.title}</div>
            <div class="mp-track-mood">${mood.icon} ${mood.label}</div>
          </div>
          <span class="mp-track-num">${state.trackIdx + 1} / ${mood.tracks.length}</span>
        </div>
      </div>

      <!-- Progress bar -->
      <div class="mp-progress-wrap">
        <div class="mp-progress-track" id="mp-progress-track">
          <div class="mp-progress-fill"></div>
        </div>
        <div class="mp-time-row">
          <span id="mp-time-cur">0:00</span>
          <span id="mp-time-tot">0:00</span>
        </div>
      </div>

      <!-- Visualizer -->
      <div class="mp-visualizer" aria-hidden="true">
        ${buildVisBars()}
      </div>

      <!-- Controls -->
      <div class="mp-controls">
        <button class="mp-btn mp-btn-sm" id="mp-prev-btn" title="Previous track" aria-label="Previous track">
          <i class="fas fa-step-backward"></i>
        </button>
        <button class="mp-btn mp-btn-lg" id="mp-play-btn" title="Play / Pause" aria-label="Play or pause">
          <i class="fas fa-play"></i>
        </button>
        <button class="mp-btn mp-btn-sm" id="mp-next-btn" title="Next track" aria-label="Next track">
          <i class="fas fa-step-forward"></i>
        </button>
      </div>

      <!-- Volume row -->
      <div class="mp-volume-row">
        <button class="mp-vol-icon" id="mp-mute-btn" title="Mute / Unmute" aria-label="Mute or unmute">
          <i class="fas fa-volume-down"></i>
        </button>
        <input type="range" class="mp-vol-slider" id="mp-vol-slider"
          min="0" max="1" step="0.01" value="${state.volume}"
          aria-label="Volume control">
        <span class="mp-vol-pct" id="mp-vol-pct">${Math.round(state.volume * 100)}%</span>
      </div>

      <!-- Track list toggle -->
      <div class="mp-footer-toggle" id="mp-list-toggle" role="button" aria-expanded="false" tabindex="0">
        <i class="fas fa-list-ul"></i>
        <span>Track List</span>
        <i class="fas fa-chevron-up" style="margin-left:auto"></i>
      </div>

      <!-- Track list -->
      <div class="mp-tracklist" id="mp-tracklist" role="list">
        ${buildTrackListItems()}
      </div>

    </div>
    `;

        const container = document.createElement('div');
        container.id = 'mp-root';
        container.innerHTML = html;
        document.body.appendChild(container);
    }

    function rebuildTrackList() {
        const list = document.getElementById('mp-tracklist');
        if (list) {
            list.innerHTML = buildTrackListItems();
            bindTrackItems();
        }
    }

    /* ── BIND EVENTS ──────────────────────────────────────────────────── */
    function bindEvents() {
        // Bubble toggle
        document.getElementById('music-bubble').addEventListener('click', togglePanel);

        // Close
        document.getElementById('mp-close-btn').addEventListener('click', () => {
            state.open = false;
            document.getElementById('music-player').classList.remove('open');
        });

        // Playback controls
        document.getElementById('mp-play-btn').addEventListener('click', togglePlay);
        document.getElementById('mp-next-btn').addEventListener('click', nextTrack);
        document.getElementById('mp-prev-btn').addEventListener('click', prevTrack);

        // Mood tabs
        document.querySelectorAll('.mp-mood-btn').forEach(btn => {
            btn.addEventListener('click', () => setMood(btn.dataset.mood));
        });

        // Volume slider
        const volSlider = document.getElementById('mp-vol-slider');
        volSlider.addEventListener('input', () => {
            state.volume = parseFloat(volSlider.value);
            state.muted  = false;
            audio.volume = state.volume;
            updateVolumeUI();
            saveState();
        });

        // Mute button
        document.getElementById('mp-mute-btn').addEventListener('click', () => {
            state.muted = !state.muted;
            audio.volume = state.muted ? 0 : state.volume;
            updateVolumeUI();
            saveState();
        });

        // Progress bar seek
        document.getElementById('mp-progress-track').addEventListener('click', (e) => {
            if (!audio.duration) return;
            const rect = e.currentTarget.getBoundingClientRect();
            const pct  = (e.clientX - rect.left) / rect.width;
            audio.currentTime = pct * audio.duration;
        });

        // Track list toggle
        const listToggle = document.getElementById('mp-list-toggle');
        listToggle.addEventListener('click', () => {
            state.listOpen = !state.listOpen;
            document.getElementById('mp-tracklist').classList.toggle('open', state.listOpen);
            listToggle.classList.toggle('open', state.listOpen);
            listToggle.setAttribute('aria-expanded', state.listOpen);
        });
        listToggle.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') listToggle.click();
        });

        // Track items
        bindTrackItems();

        // Logout: intercept logout links to stop music
        document.querySelectorAll('a[href*="auth/logout"]').forEach(link => {
            link.addEventListener('click', () => {
                audio.pause();
                audio.src = '';
                localStorage.removeItem('mp_mood');
                localStorage.removeItem('mp_track');
                localStorage.removeItem('mp_muted');
            });
        });

        // Keyboard shortcut: Space toggles play (only when player is focused)
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (e.code === 'Space' && e.altKey) {
                e.preventDefault();
                togglePlay();
            }
        });
    }

    function bindTrackItems() {
        document.querySelectorAll('.mp-track-item').forEach(el => {
            el.addEventListener('click', () => {
                setTrack(parseInt(el.dataset.idx, 10));
                if (!state.playing) play();
            });
        });
    }

    function togglePanel() {
        state.open = !state.open;
        document.getElementById('music-player').classList.toggle('open', state.open);
    }

    /* ── INIT ─────────────────────────────────────────────────────────── */
    function init() {
        buildPlayer();
        loadTrack(false);
        updateVolumeUI();
        updateMoodUI();
        updatePlayUI();

        bindEvents();

        // Auto-play on page load if was playing before (browsers may block this)
        // We'll just keep it paused until user clicks
        // Volume slider background
        updateVolSliderBg();

        // Show player briefly after login (first visit)
        const firstVisit = !sessionStorage.getItem('mp_shown');
        if (firstVisit) {
            sessionStorage.setItem('mp_shown', '1');
            setTimeout(() => {
                state.open = true;
                document.getElementById('music-player').classList.add('open');
            }, 800);
        }
    }

    // Wait for DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();