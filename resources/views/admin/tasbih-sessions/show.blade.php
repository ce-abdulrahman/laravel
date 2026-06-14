@extends('layouts.app')
@section('title', __('sessions.title'))
@section('page-title', __('sessions.title'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sessions.index') }}">{{ __('sessions.title') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Session details #{{ $session->id }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">🧘 Session Replay #{{ $session->id }}</h1>
            <div class="text-muted small">User: <strong>{{ $session->user?->name }}</strong> ({{ $session->user?->email }})</div>
        </div>
        <div class="d-flex gap-2">
            @if($session->status !== 'completed')
                <form method="POST" action="{{ route('admin.sessions.force-close', $session->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning d-flex align-items-center gap-2" onclick="return confirm('Force close this active session?')">
                        <i class="bi bi-slash-circle"></i> Force Close Session
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.sessions.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    {{-- Detail Info Card Grid --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-card p-4">
                <div class="text-muted small fw-semibold">Dhikr Type</div>
                <h4 class="mt-2 text-primary fw-bold">
                    {{ $session->dhikr?->name ?? $session->custom_dhikr_name ?? 'General Counting' }}
                </h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-card p-4">
                <div class="text-muted small fw-semibold">Total Taps count</div>
                <h4 class="mt-2 text-dark fw-bold" id="kpi-taps-display">
                    {{ number_format($session->total_count) }}
                </h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-card p-4">
                <div class="text-muted small fw-semibold">Active Duration</div>
                <h4 class="mt-2 text-dark fw-bold">
                    {{ (int) floor($session->duration_seconds / 60) }} min {{ $session->duration_seconds % 60 }} sec
                </h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-card p-4">
                <div class="text-muted small fw-semibold">Average Intensity Rate</div>
                <h4 class="mt-2 text-success fw-bold">
                    {{ $session->avg_per_minute }} taps / min
                </h4>
            </div>
        </div>
    </div>

    {{-- Interactive Replay Scrubber Screen --}}
    <div class="quran-card p-4 mb-4" style="background: #0f172a; color: #e2e8f0; border: 1px solid #334155;">
        <h5 class="fw-semibold mb-3 text-info"><i class="bi bi-play-btn me-2"></i> Session Replay Scrubber</h5>

        {{-- Scrubber Graphic Display Area --}}
        <div class="position-relative p-3 mb-4 rounded-3" style="background: #1e293b; height: 180px; overflow: hidden;" id="scrubber-visual-container">
            {{-- Canvas for draw lines --}}
            <canvas id="scrubber-canvas" class="w-100 h-100 position-absolute start-0 top-0"></canvas>
            
            {{-- Scrubber Line marker --}}
            <div id="scrubber-bar" class="position-absolute h-100" style="width: 2px; background: #fb7185; top: 0; left: 0%; pointer-events: none; box-shadow: 0 0 8px #fb7185; z-index: 10;"></div>
            
            {{-- Live Value Hover Card --}}
            <div id="scrubber-tooltip" class="position-absolute bg-dark text-white rounded p-2 small border border-secondary" style="bottom: 10px; left: 0%; transform: translateX(-50%); display: none; pointer-events: none; z-index: 20;">
                Time: <span id="tooltip-time">0:00</span> | Taps: <span id="tooltip-taps">0</span>
            </div>
        </div>

        {{-- Scrubber Scroller Bar --}}
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="small text-muted" id="play-time-current">0:00</span>
            <input type="range" class="form-range flex-grow-1" id="scrubber-slider" min="0" max="{{ $scrubberData['duration'] }}" value="0" style="accent-color: #fb7185;">
            <span class="small text-muted">{{ sprintf('%d:%02d', floor($scrubberData['duration'] / 60), $scrubberData['duration'] % 60) }}</span>
        </div>

        {{-- Control buttons --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-info text-dark fw-bold d-flex align-items-center gap-1" id="btn-play-pause">
                    <i class="bi bi-play-fill" id="play-icon"></i> Play Session
                </button>
                <button class="btn btn-sm btn-outline-secondary text-light" id="btn-stop">
                    <i class="bi bi-stop-fill"></i> Stop
                </button>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Playback Speed:</span>
                <select class="form-select form-select-sm bg-secondary text-white border-0" id="playback-speed" style="width: 80px;">
                    <option value="1">1x</option>
                    <option value="2">2x</option>
                    <option value="5">5x</option>
                    <option value="10">10x</option>
                </select>
            </div>

            <div class="small">
                Live Tap Rate: <strong class="text-warning"><span id="live-rate-display">0</span> taps / min</strong>
            </div>
        </div>
    </div>

    {{-- Details Timeline Event list --}}
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="quran-card p-4">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2"><i class="bi bi-journal-text"></i> Session Timeline Logs</h5>
                <div class="overflow-auto" style="max-height: 400px;">
                    <ul class="list-group list-group-flush" id="timeline-list">
                        @foreach($logs as $index => $log)
                            @php
                                $offset = strtotime($log->timestamp) - strtotime($session->start_time);
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 timeline-item" data-offset="{{ $offset }}" id="log-item-{{ $index }}">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge {{ $log->event_type === 'start' || $log->event_type === 'end' ? 'bg-primary' : ($log->event_type === 'pause' ? 'bg-warning' : ($log->event_type === 'resume' ? 'bg-info' : 'bg-secondary')) }} rounded-pill">
                                        {{ strtoupper($log->event_type) }}
                                    </span>
                                    <div>
                                        <div class="small text-dark fw-bold">
                                            @if($log->event_type === 'increment')
                                                Incremented count by +{{ $log->value }}
                                            @elseif($log->event_type === 'start')
                                                Session initiated
                                            @elseif($log->event_type === 'pause')
                                                Dhikr paused
                                            @elseif($log->event_type === 'resume')
                                                Dhikr resumed
                                            @else
                                                Session finalized
                                            @endif
                                        </div>
                                        <span class="text-muted small">UUID: {{ substr($log->event_uuid, 0, 8) }}...</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="small text-dark fw-bold">{{ date('H:i:s', strtotime($log->timestamp)) }}</div>
                                    <span class="text-muted small">+{{ (int) floor($offset / 60) }}m {{ $offset % 60 }}s offset</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">📋 Details & Settings</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted">Status</span>
                        <span class="badge {{ $session->status === 'completed' ? 'bg-success' : 'bg-warning' }}">{{ strtoupper($session->status) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted">Start Time (UTC)</span>
                        <span class="text-dark fw-bold">{{ $session->start_time->format('Y-m-d H:i:s') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted">End Time (UTC)</span>
                        <span class="text-dark fw-bold">{{ $session->end_time ? $session->end_time->format('Y-m-d H:i:s') : '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted">Session Date (Local)</span>
                        <span class="text-dark fw-bold">{{ $session->session_date->toDateString() }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Interactive timeline play logic --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scrubberData = @json($scrubberData);
    const canvas = document.getElementById('scrubber-canvas');
    const ctx = canvas.getContext('2d');
    const scrubberBar = document.getElementById('scrubber-bar');
    const scrubberSlider = document.getElementById('scrubber-slider');
    const tooltip = document.getElementById('scrubber-tooltip');
    const tooltipTime = document.getElementById('tooltip-time');
    const tooltipTaps = document.getElementById('tooltip-taps');
    const playTimeCurrent = document.getElementById('play-time-current');
    const btnPlayPause = document.getElementById('btn-play-pause');
    const btnStop = document.getElementById('btn-stop');
    const playIcon = document.getElementById('play-icon');
    const playbackSpeed = document.getElementById('playback-speed');
    const liveRateDisplay = document.getElementById('live-rate-display');
    const timelineItems = document.querySelectorAll('.timeline-item');
    const kpiTapsDisplay = document.getElementById('kpi-taps-display');

    let isPlaying = false;
    let playTimer = null;
    let currentSecond = 0;

    // Resize canvas
    function resizeCanvas() {
        canvas.width = canvas.parentElement.clientWidth;
        canvas.height = canvas.parentElement.clientHeight;
        drawTimeline();
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    // Draw visual bins on Canvas
    function drawTimeline() {
        if (!scrubberData.points || scrubberData.points.length === 0) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const width = canvas.width;
        const height = canvas.height;
        const pts = scrubberData.points;
        const numPts = pts.length;
        const barWidth = width / numPts;

        // Find max taps in a bin to normalize height
        const maxTaps = Math.max(...pts.map(p => p.taps), 1);

        // Draw graph background bars
        for (let i = 0; i < numPts; $i++) {
            const p = pts[i];
            const barHeight = (p.taps / maxTaps) * (height - 60);
            const x = i * barWidth;
            const y = height - barHeight - 30;

            ctx.fillStyle = '#334155';
            ctx.fillRect(x, y, barWidth - 1, barHeight);
        }

        // Draw line representing taps frequency (smooth path)
        ctx.beginPath();
        ctx.strokeStyle = '#38bdf8';
        ctx.lineWidth = 2;
        for (let i = 0; i < numPts; $i++) {
            const p = pts[i];
            const valH = (p.taps / maxTaps) * (height - 60);
            const x = (i * barWidth) + (barWidth / 2);
            const y = height - valH - 30;

            if (i === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        }
        ctx.stroke();

        // Draw markers on canvas
        scrubberData.markers.forEach(m => {
            const pct = m.offset_seconds / scrubberData.duration;
            const x = pct * width;
            
            ctx.beginPath();
            ctx.strokeStyle = m.type === 'pause' ? '#eab308' : (m.type === 'resume' ? '#06b6d4' : '#6366f1');
            ctx.lineWidth = 1;
            ctx.setLineDash([4, 4]);
            ctx.moveTo(x, 0);
            ctx.lineTo(x, height - 30);
            ctx.stroke();
            ctx.setLineDash([]);

            // Draw text tag for markers
            ctx.fillStyle = '#94a3b8';
            ctx.font = '10px Cairo';
            ctx.fillText(m.type.toUpperCase(), x + 3, 20);
        });
    }

    // Update scrubber visuals
    function updateScrubber(seconds) {
        currentSecond = seconds;
        scrubberSlider.value = seconds;

        const pct = (seconds / scrubberData.duration) * 100;
        scrubberBar.style.left = `${pct}%`;

        // Format time
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        playTimeCurrent.textContent = `${m}:${s.toString().padStart(2, '0')}`;

        // Find current taps bin
        const binIndex = Math.floor(seconds / 2);
        if (scrubberData.points && scrubberData.points[binIndex]) {
            const pt = scrubberData.points[binIndex];
            liveRateDisplay.textContent = pt.rate_per_minute;
            
            // Highlight tooltip
            tooltip.style.display = 'block';
            tooltip.style.left = `${pct}%`;
            tooltipTime.textContent = `${m}:${s.toString().padStart(2, '0')}`;
            tooltipTaps.textContent = pt.taps;
        }

        // Highlight active log item in list based on scroll offset
        timelineItems.forEach(item => {
            const offset = parseInt(item.getAttribute('data-offset'));
            if (offset <= seconds) {
                item.classList.add('bg-secondary-subtle');
                item.classList.add('bg-opacity-20');
            } else {
                item.classList.remove('bg-secondary-subtle');
                item.classList.remove('bg-opacity-20');
            }
        });
    }

    // Playback loop
    function play() {
        if (!isPlaying) return;

        const speed = parseInt(playbackSpeed.value);
        let nextSecond = currentSecond + 1;

        if (nextSecond > scrubberData.duration) {
            stop();
            return;
        }

        updateScrubber(nextSecond);
        playTimer = setTimeout(play, 1000 / speed);
    }

    function startPlay() {
        isPlaying = true;
        playIcon.className = 'bi bi-pause-fill';
        btnPlayPause.innerHTML = '<i class="bi bi-pause-fill"></i> Pause';
        play();
    }

    function pausePlay() {
        isPlaying = false;
        clearTimeout(playTimer);
        playIcon.className = 'bi bi-play-fill';
        btnPlayPause.innerHTML = '<i class="bi bi-play-fill"></i> Play Session';
    }

    function stop() {
        pausePlay();
        updateScrubber(0);
        tooltip.style.display = 'none';
        liveRateDisplay.textContent = '0';
    }

    // Interactive slider listener
    scrubberSlider.addEventListener('input', function(e) {
        pausePlay();
        updateScrubber(parseInt(e.target.value));
    });

    btnPlayPause.addEventListener('click', function() {
        if (isPlaying) {
            pausePlay();
        } else {
            startPlay();
        }
    });

    btnStop.addEventListener('click', stop);
});
</script>
@endsection
