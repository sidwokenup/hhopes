document.addEventListener('DOMContentLoaded', function() {
    // Check if Three.js is loaded
    if (typeof THREE === 'undefined') {
        console.error('Three.js library not found!');
        return;
    }

    let scene, camera, renderer, particleSystem;
    const particleCount = 2000;

    function initThreeJS() {
        const canvas = document.getElementById('three-canvas');
        if (!canvas) return;

        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 5;

        renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true });
        renderer.setSize(window.innerWidth, window.innerHeight);

        const particles = new THREE.BufferGeometry();
        const positions = new Float32Array(particleCount * 3);

        for (let i = 0; i < particleCount * 3; i++) {
            positions[i] = (Math.random() - 0.5) * 20;
        }

        particles.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        
        const particleMaterial = new THREE.PointsMaterial({
            color: 0x34d399, // Neon Green
            size: 0.02,
            blending: THREE.AdditiveBlending,
            transparent: true,
            opacity: 0.8
        });
        
        particleSystem = new THREE.Points(particles, particleMaterial);
        scene.add(particleSystem);
    }

    function animate() {
        requestAnimationFrame(animate);
        if (particleSystem) {
             particleSystem.rotation.y += 0.0002;
             particleSystem.rotation.x += 0.0001;
        }
        if(renderer && scene && camera){
            renderer.render(scene, camera);
        }
    }
    
    function onWindowResize() {
        if(camera && renderer) {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }
    }

    window.addEventListener('resize', onWindowResize, false);

    initThreeJS();
    animate();
});
