window.behaviorCollector = {
    typingTimes: [],
    mousePositions: [],
    lastMouseTime: null,

    init() {
        document.addEventListener('keyup', (e) => {
            this.typingTimes.push(Date.now());
        });

        document.addEventListener('mousemove', (e) => {
            this.mousePositions.push({ x: e.clientX, y: e.clientY, time: Date.now() });
        });

        setInterval(() => this.submit(), 60000);
    },

    submit() {
        if (this.typingTimes.length < 2 && this.mousePositions.length < 2) return;

        let typingSpeed = 0;
        if (this.typingTimes.length >= 2) {
            const intervals = [];
            for (let i = 1; i < this.typingTimes.length; i++) {
                intervals.push(this.typingTimes[i] - this.typingTimes[i-1]);
            }
            const avgInterval = intervals.reduce((a,b) => a+b, 0) / intervals.length;
            typingSpeed = avgInterval > 0 ? 1000 / avgInterval : 0;
        }

        let mouseVelocity = 0;
        if (this.mousePositions.length >= 2) {
            let totalDist = 0, totalTime = 0;
            for (let i = 1; i < this.mousePositions.length; i++) {
                const dx = this.mousePositions[i].x - this.mousePositions[i-1].x;
                const dy = this.mousePositions[i].y - this.mousePositions[i-1].y;
                totalDist += Math.sqrt(dx*dx + dy*dy);
                totalTime += this.mousePositions[i].time - this.mousePositions[i-1].time;
            }
            mouseVelocity = totalTime > 0 ? totalDist / totalTime : 0;
        }

        fetch('/api/behavior/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                avg_mouse_speed: mouseVelocity,
                avg_mouse_acceleration: 0,
                avg_dwell_time: typingSpeed > 0 ? (1000 / typingSpeed) : 0,
                avg_flight_time: 0,
                mouse_event_count: this.mousePositions.length,
                key_event_count: this.typingTimes.length
            })
        });

        this.typingTimes = [];
        this.mousePositions = [];
    }
};

document.addEventListener('DOMContentLoaded', () => behaviorCollector.init());
