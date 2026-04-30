class BehaviorSensor {
    constructor() {
        this.mouseEvents = [];
        this.keyEvents = [];
        this.keyStates = {}; // Track active keys for dwell time
        this.lastKeyPressTime = null;
        this.lastMousePos = null;
        
        this.initEventListeners();
        this.startReporting();
    }

    initEventListeners() {
        // Mouse tracking
        document.addEventListener('mousemove', (e) => this.trackMouse(e));
        
        // Keyboard tracking
        document.addEventListener('keydown', (e) => this.trackKeyDown(e));
        document.addEventListener('keyup', (e) => this.trackKeyUp(e));
    }

    trackMouse(e) {
        const now = Date.now();
        const currentPos = { x: e.clientX, y: e.clientY, t: now };
        
        if (this.lastMousePos) {
            const dx = currentPos.x - this.lastMousePos.x;
            const dy = currentPos.y - this.lastMousePos.y;
            const dt = currentPos.t - this.lastMousePos.t;
            
            if (dt > 0) {
                const distance = Math.sqrt(dx * dx + dy * dy);
                const speed = distance / dt;
                const angle = Math.atan2(dy, dx);
                
                this.mouseEvents.push({ speed, angle, dt });
            }
        }
        this.lastMousePos = currentPos;
    }

    trackKeyDown(e) {
        const key = e.key;
        const now = Date.now();
        
        if (!this.keyStates[key]) {
            this.keyStates[key] = now; // Start dwell time
        }
        
        if (this.lastKeyPressTime) {
            const flightTime = now - this.lastKeyPressTime;
            this.keyEvents.push({ type: 'flight', time: flightTime });
        }
        this.lastKeyPressTime = now;
    }

    trackKeyUp(e) {
        const key = e.key;
        const now = Date.now();
        
        if (this.keyStates[key]) {
            const dwellTime = now - this.keyStates[key];
            this.keyEvents.push({ type: 'dwell', time: dwellTime });
            delete this.keyStates[key];
        }
    }

    aggregateFeatures() {
        // Calculate average mouse speed and acceleration
        let totalSpeed = 0;
        let totalAcceleration = 0;
        let validMouseEvents = 0;

        for (let i = 0; i < this.mouseEvents.length; i++) {
            totalSpeed += this.mouseEvents[i].speed;
            if (i > 0) {
                const dv = this.mouseEvents[i].speed - this.mouseEvents[i-1].speed;
                const dt = this.mouseEvents[i].dt;
                if (dt > 0) {
                    totalAcceleration += Math.abs(dv / dt);
                }
            }
            validMouseEvents++;
        }

        const avgMouseSpeed = validMouseEvents ? totalSpeed / validMouseEvents : 0;
        const avgMouseAcceleration = validMouseEvents > 1 ? totalAcceleration / (validMouseEvents - 1) : 0;

        // Calculate average dwell and flight times
        let totalDwell = 0;
        let dwellCount = 0;
        let totalFlight = 0;
        let flightCount = 0;

        this.keyEvents.forEach(evt => {
            if (evt.type === 'dwell') {
                totalDwell += evt.time;
                dwellCount++;
            } else if (evt.type === 'flight') {
                totalFlight += evt.time;
                flightCount++;
            }
        });

        const avgDwellTime = dwellCount ? totalDwell / dwellCount : 0;
        const avgFlightTime = flightCount ? totalFlight / flightCount : 0;

        // Clear arrays after aggregation
        this.mouseEvents = [];
        this.keyEvents = [];

        return {
            avg_mouse_speed: avgMouseSpeed,
            avg_mouse_acceleration: avgMouseAcceleration,
            avg_dwell_time: avgDwellTime,
            avg_flight_time: avgFlightTime,
            mouse_event_count: validMouseEvents,
            key_event_count: dwellCount + flightCount
        };
    }

    startReporting() {
        setInterval(() => {
            const features = this.aggregateFeatures();
            
            // Only send if there's actual activity
            if (features.mouse_event_count > 0 || features.key_event_count > 0) {
                this.sendToAPI(features);
            }
        }, 5000);
    }

    sendToAPI(features) {
        // We use axios if available, otherwise fallback to fetch
        if (window.axios) {
            window.axios.post('/api/behavior/verify', features)
                .catch(err => console.error('Behavior tracking error:', err));
        } else {
            fetch('/api/behavior/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(features)
            }).catch(err => console.error('Behavior tracking error:', err));
        }
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    // We only want to track if there's a meta CSRF token (typically means it's a blade view)
    // and potentially if we are logged in.
    if (document.querySelector('meta[name="csrf-token"]')) {
        window.behaviorSensor = new BehaviorSensor();
    }
});
