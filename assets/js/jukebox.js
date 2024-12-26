let trees = [];
let snowSets = [];
let plane;
let animationFrameId;
let zhe;

const { PI, sin, cos } = Math;
const TAU = 2 * PI;

const map = (value, sMin, sMax, dMin, dMax) => {
    return dMin + ((value - sMin) / (sMax - sMin)) * (dMax - dMin);
};

const range = (n, m = 0) =>
    Array(n)
        .fill(m)
        .map((i, j) => i + j);

const rand = (max, min = 0) => min + Math.random() * (max - min);
const randInt = (max, min = 0) => Math.floor(min + Math.random() * (max - min));
const randChoise = (arr) => arr[randInt(arr.length)];
const polar = (ang, r = 1) => [r * cos(ang), r * sin(ang)];

let scene, camera, renderer, analyser;
let step = 0;
const uniforms = {
    time: { type: "f", value: 0.0 },
    step: { type: "f", value: 0.0 },
};
const params = {
    exposure: 1,
    bloomStrength: 0.9,
    bloomThreshold: 0,
    bloomRadius: 0.5,
};
let composer;

const fftSize = 2048;
const totalPoints = 4000;

let listener = new THREE.AudioListener();
let audio = new THREE.Audio(listener);

const pluginBaseUrl = '/wp-content/plugins/holiday-jukebox/';

document.addEventListener("DOMContentLoaded", () => {
    window.loadAlbumOverlay = function (albumName) {
        // Fetch album content via AJAX
        fetch(`/wp-admin/admin-ajax.php?action=get_album_content&album=${encodeURIComponent(albumName)}`)
            .then(response => response.text())
            .then(data => {
                const overlay = document.getElementById('overlay');
                overlay.innerHTML = data;
                overlay.style.display = 'inline-flex';
            })
            .catch(error => console.error('Error loading album:', error));
    };

    window.loadAlbum = function (albumName) {
        loadAlbumOverlay(albumName); // Use the same function to load albums dynamically
    };
    document.getElementById("backButton").addEventListener("click", stopAudio);
});

function addZhe(scene) {
    const map = new THREE.TextureLoader().load(pluginBaseUrl + 'assets/images/album01.png'); // Replace with your png path
    const material = new THREE.SpriteMaterial({
        map: map,
        transparent: false,
        opacity: 1,
        color: 0x680000
    });

    zhe = new THREE.Sprite(material);
    zhe.position.set(0, -20, -20);
    zhe.scale.set(10, 11, 1);
    scene.add(zhe);
}

function init() {
    const overlay = document.getElementById("overlay");

    const jukeboxContainer = document.getElementById("jukebox-container");
    const container = document.createElement('div'); // Specify 'div' as the type of element
    container.className = "jukebox-animation"; // Use className to assign a class
    jukeboxContainer.appendChild(container); // Append the element to the body


    overlay.style.display = 'none';
    document.getElementById("backButton").style.display = 'block';

    scene = new THREE.Scene();
    renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.setSize(window.innerWidth, window.innerHeight);
    container.appendChild(renderer.domElement);

    camera = new THREE.PerspectiveCamera(
        60,
        window.innerWidth / window.innerHeight,
        1,
        1000
    );
    camera.position.set(-0.09397456774197047, -2.5597086635726947, 24.420789670889008)
    camera.rotation.set(0.10443543723052419, -0.003827152981119352, 0.0004011488708739715)

    const format = renderer.capabilities.isWebGL2
        ? THREE.RedFormat
        : THREE.LuminanceFormat;

    uniforms.tAudioData = {
        value: new THREE.DataTexture(analyser.data, fftSize / 2, 1, format),
    };

    addPlane(scene, uniforms, 3000);
    addSnow(scene, uniforms);
    addZhe(scene);

    range(10).map((i) => {
        addTree(scene, uniforms, totalPoints, [20, 0, -20 * i]);
        addTree(scene, uniforms, totalPoints, [-20, 0, -20 * i]);
    });

    const renderScene = new THREE.RenderPass(scene, camera);

    const bloomPass = new THREE.UnrealBloomPass(
        new THREE.Vector2(window.innerWidth, window.innerHeight),
        1.5,
        0.4,
        0.85
    );
    bloomPass.threshold = params.bloomThreshold;
    bloomPass.strength = params.bloomStrength;
    bloomPass.radius = params.bloomRadius;

    composer = new THREE.EffectComposer(renderer);
    composer.addPass(renderScene);
    composer.addPass(bloomPass);

    addListners(camera, renderer, composer);
    animate();
}

function animate(time) {
    analyser.getFrequencyData();
    uniforms.tAudioData.value.needsUpdate = true;
    step = (step + 1) % 1000;
    uniforms.time.value = time;
    uniforms.step.value = step;

    if (zhe) {
        zhe.position.y = 0.01 + Math.sin(time * 0.001) * 0.1;
    }

    composer.render();
    animationFrameId = requestAnimationFrame(animate);
}
function loadAudio(button) {
    listener.context.resume().then(() => {
        var audioSrc = button.getAttribute('data-filepath');

        // Check if the audio has a valid currentSrc (indicating the audio is loaded)
        if (audioSrc) {
            // Create a new THREE.Audio object
            audio = new THREE.Audio(listener);

            // Use THREE.AudioLoader to load the audio into the buffer
            const loader = new THREE.AudioLoader();
            loader.load(audioSrc, function (buffer) {
                // Set the loaded buffer to the audio
                audio.setBuffer(buffer);
                audio.play();
                analyser = new THREE.AudioAnalyser(audio, fftSize);
                init();
            }, undefined, function (error) {
                console.error('Error loading audio:', error);
            });
        } else {
            console.error("Audio source (currentSrc) is not set.");
        }
    }).catch(err => {
        console.error('Audio context resume failed:', err);
    });
}


function stopAudio() {
    // First stop the audio
    if (audio) {
        audio.stop();
    }

    // Hide back button
    const backButton = document.getElementById("backButton");
    if (backButton) {
        backButton.style.display = 'none';
    }

    // Clean up Three.js objects
    cleanupScene();



    // Show overlay with proper positioning
    const overlay = document.getElementById("overlay");
    overlay.style.display = 'flex';

    // Reattach event listeners to the newly added buttons
    const buttons = document.querySelectorAll(".btn");
    buttons.forEach((button, index) => {
        button.addEventListener("click", function () {
            loadAudio(this);
        });
    });


    if (renderer) {
        renderer.domElement.remove();
    }
}

function uploadAudio(event) {
    document.getElementById("overlay").innerHTML =
        '<div class="text-loading">Please Wait...</div>';
    const files = event.target.files;
    const reader = new FileReader();

    reader.onload = function (file) {
        var arrayBuffer = file.target.result;

        listener.context.decodeAudioData(arrayBuffer, function (audioBuffer) {
            audio.setBuffer(audioBuffer);
            audio.play();
            analyser = new THREE.AudioAnalyser(audio, fftSize);
            init();
        });
    };

    reader.readAsArrayBuffer(files[0]);
}

function addTree(scene, uniforms, totalPoints, treePosition) {
    const vertexShader = `
 attribute float mIndex;
 varying vec3 vColor;
 varying float opacity;
 uniform sampler2D tAudioData;

 float norm(float value, float min, float max ){
 return (value - min) / (max - min);
 }
 float lerp(float norm, float min, float max){
 return (max - min) * norm + min;
 }

 float map(float value, float sourceMin, float sourceMax, float destMin, float destMax){
 return lerp(norm(value, sourceMin, sourceMax), destMin, destMax);
 }


 void main() {
 vColor = color;
 vec3 p = position;
 vec4 mvPosition = modelViewMatrix * vec4( p, 1.0 );
 float amplitude = texture2D( tAudioData, vec2( mIndex, 0.1 ) ).r;
 float amplitudeClamped = clamp(amplitude-0.4,0.0, 0.6 );
 float sizeMapped = map(amplitudeClamped, 0.0, 0.6, 1.0, 20.0);
 opacity = map(mvPosition.z , -200.0, 15.0, 0.0, 1.0);
 gl_PointSize = sizeMapped * ( 100.0 / -mvPosition.z );
 gl_Position = projectionMatrix * mvPosition;
 }
 `;
    const fragmentShader = `
 varying vec3 vColor;
 varying float opacity;
 uniform sampler2D pointTexture;
 void main() {
 gl_FragColor = vec4( vColor, opacity );
 gl_FragColor = gl_FragColor * texture2D( pointTexture, gl_PointCoord );
 }
 `;
    const shaderMaterial = new THREE.ShaderMaterial({
        uniforms: {
            ...uniforms,
            pointTexture: {
                value: new THREE.TextureLoader().load(`https://assets.codepen.io/3685267/spark1.png`),
            },
        },
        vertexShader,
        fragmentShader,
        blending: THREE.AdditiveBlending,
        depthTest: false,
        transparent: true,
        vertexColors: true,
    });

    const geometry = new THREE.BufferGeometry();
    const positions = [];
    const colors = [];
    const sizes = [];
    const phases = [];
    const mIndexs = [];

    const color = new THREE.Color();

    for (let i = 0; i < totalPoints; i++) {
        const t = Math.random();
        const y = map(t, 0, 1, -8, 10);
        const ang = map(t, 0, 1, 0, 6 * TAU) + (TAU / 2) * (i % 2);
        const [z, x] = polar(ang, map(t, 0, 1, 5, 0));

        const modifier = map(t, 0, 1, 1, 0);
        positions.push(x + rand(-0.3 * modifier, 0.3 * modifier));
        positions.push(y + rand(-0.3 * modifier, 0.3 * modifier));
        positions.push(z + rand(-0.3 * modifier, 0.3 * modifier));

        color.setHSL(map(i, 0, totalPoints, 1.0, 0.0), 1.0, 0.5);

        colors.push(color.r, color.g, color.b);
        phases.push(rand(1000));
        sizes.push(1);
        const mIndex = map(i, 0, totalPoints, 1.0, 0.0);
        mIndexs.push(mIndex);
    }

    geometry.setAttribute(
        "position",
        new THREE.Float32BufferAttribute(positions, 3).setUsage(
            THREE.DynamicDrawUsage
        )
    );
    geometry.setAttribute("color", new THREE.Float32BufferAttribute(colors, 3));
    geometry.setAttribute("size", new THREE.Float32BufferAttribute(sizes, 1));
    geometry.setAttribute("phase", new THREE.Float32BufferAttribute(phases, 1));
    geometry.setAttribute("mIndex", new THREE.Float32BufferAttribute(mIndexs, 1));

    const tree = new THREE.Points(geometry, shaderMaterial);

    const [px, py, pz] = treePosition;

    tree.position.x = px;
    tree.position.y = py;
    tree.position.z = pz;

    scene.add(tree);
    trees.push(tree); // Store reference to the tree
}

function addSnow(scene, uniforms) {
    const vertexShader = `
 attribute float size;
 attribute float phase;
 attribute float phaseSecondary;

 varying vec3 vColor;
 varying float opacity;


 uniform float time;
 uniform float step;

 float norm(float value, float min, float max ){
 return (value - min) / (max - min);
 }
 float lerp(float norm, float min, float max){
 return (max - min) * norm + min;
 }

 float map(float value, float sourceMin, float sourceMax, float destMin, float destMax){
 return lerp(norm(value, sourceMin, sourceMax), destMin, destMax);
 }
 void main() {
 float t = time* 0.0006;

 vColor = color;

 vec3 p = position;

 p.y = map(mod(phase+step, 1000.0), 0.0, 1000.0, 25.0, -8.0);

 p.x += sin(t+phase);
 p.z += sin(t+phaseSecondary);

 opacity = map(p.z, -150.0, 15.0, 0.0, 1.0);

 vec4 mvPosition = modelViewMatrix * vec4( p, 1.0 );

 gl_PointSize = size * ( 100.0 / -mvPosition.z );

 gl_Position = projectionMatrix * mvPosition;

 }
 `;

    const fragmentShader = `
 uniform sampler2D pointTexture;
 varying vec3 vColor;
 varying float opacity;

 void main() {
 gl_FragColor = vec4( vColor, opacity );
 gl_FragColor = gl_FragColor * texture2D( pointTexture, gl_PointCoord );
 }
 `;
    function createSnowSet(sprite) {
        const totalPoints = 300;
        const shaderMaterial = new THREE.ShaderMaterial({
            uniforms: {
                ...uniforms,
                pointTexture: {
                    value: new THREE.TextureLoader().load(sprite),
                },
            },
            vertexShader,
            fragmentShader,
            blending: THREE.AdditiveBlending,
            depthTest: false,
            transparent: true,
            vertexColors: true,
        });

        const geometry = new THREE.BufferGeometry();
        const positions = [];
        const colors = [];
        const sizes = [];
        const phases = [];
        const phaseSecondaries = [];

        const color = new THREE.Color();

        for (let i = 0; i < totalPoints; i++) {
            const [x, y, z] = [rand(25, -25), 0, rand(15, -150)];
            positions.push(x);
            positions.push(y);
            positions.push(z);

            color.set(randChoise(["#f1d4d4", "#f1f6f9", "#eeeeee", "#f1f1e8"]));

            colors.push(color.r, color.g, color.b);
            phases.push(rand(1000));
            phaseSecondaries.push(rand(1000));
            sizes.push(rand(4, 2));
        }

        geometry.setAttribute(
            "position",
            new THREE.Float32BufferAttribute(positions, 3)
        );
        geometry.setAttribute("color", new THREE.Float32BufferAttribute(colors, 3));
        geometry.setAttribute("size", new THREE.Float32BufferAttribute(sizes, 1));
        geometry.setAttribute("phase", new THREE.Float32BufferAttribute(phases, 1));
        geometry.setAttribute(
            "phaseSecondary",
            new THREE.Float32BufferAttribute(phaseSecondaries, 1)
        );

        const mesh = new THREE.Points(geometry, shaderMaterial);

        scene.add(mesh);
        snowSets.push(mesh); // Store reference to each snow set
    }
    const sprites = [
        "https://assets.codepen.io/3685267/snowflake1.png",
        "https://assets.codepen.io/3685267/snowflake2.png",
        "https://assets.codepen.io/3685267/snowflake3.png",
        "https://assets.codepen.io/3685267/snowflake4.png",
        "https://assets.codepen.io/3685267/snowflake5.png",
    ];
    sprites.forEach((sprite) => {
        createSnowSet(sprite);
    });
}

function addPlane(scene, uniforms, totalPoints) {
    const vertexShader = `
 attribute float size;
 attribute vec3 customColor;
 varying vec3 vColor;

 void main() {
 vColor = customColor;
 vec4 mvPosition = modelViewMatrix * vec4( position, 1.0 );
 gl_PointSize = size * ( 300.0 / -mvPosition.z );
 gl_Position = projectionMatrix * mvPosition;

 }
 `;
    const fragmentShader = `
 uniform vec3 color;
 uniform sampler2D pointTexture;
 varying vec3 vColor;

 void main() {
 gl_FragColor = vec4( vColor, 1.0 );
 gl_FragColor = gl_FragColor * texture2D( pointTexture, gl_PointCoord );

 }
 `;
    const shaderMaterial = new THREE.ShaderMaterial({
        uniforms: {
            ...uniforms,
            pointTexture: {
                value: new THREE.TextureLoader().load(`https://assets.codepen.io/3685267/spark1.png`),
            },
        },
        vertexShader,
        fragmentShader,
        blending: THREE.AdditiveBlending,
        depthTest: false,
        transparent: true,
        vertexColors: true,
    });

    const geometry = new THREE.BufferGeometry();
    const positions = [];
    const colors = [];
    const sizes = [];

    const color = new THREE.Color();

    for (let i = 0; i < totalPoints; i++) {
        const [x, y, z] = [rand(-25, 25), 0, rand(-150, 15)];
        positions.push(x);
        positions.push(y);
        positions.push(z);

        color.set(randChoise(["#93abd3", "#f2f4c0", "#9ddfd3"]));

        colors.push(color.r, color.g, color.b);
        sizes.push(1);
    }

    geometry.setAttribute(
        "position",
        new THREE.Float32BufferAttribute(positions, 3).setUsage(
            THREE.DynamicDrawUsage
        )
    );
    geometry.setAttribute(
        "customColor",
        new THREE.Float32BufferAttribute(colors, 3)
    );
    geometry.setAttribute("size", new THREE.Float32BufferAttribute(sizes, 1));

    plane = new THREE.Points(geometry, shaderMaterial);

    plane.position.y = -8;
    scene.add(plane);
}

function handleResize() {
    const width = window.innerWidth;
    const height = window.innerHeight;

    camera.aspect = width / height;
    camera.updateProjectionMatrix();

    renderer.setSize(width, height);
    composer.setSize(width, height);
}

function handleKeydown(e) {
    const { x, y, z } = camera.position;
    console.log(`camera.position.set(${x},${y},${z})`);
    const { x: a, y: b, z: c } = camera.rotation;
    console.log(`camera.rotation.set(${a},${b},${c})`);
}

// Modify addListners function to use named functions
function addListners(camera, renderer, composer) {
    document.addEventListener("keydown", handleKeydown);
    window.addEventListener("resize", handleResize, false);
}

function cleanupScene() {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
        animationFrameId = null;
    }

    // Dispose of tree geometries and materials
    trees.forEach(tree => {
        tree.geometry.dispose();
        tree.material.dispose();
        scene.remove(tree);
    });
    trees = [];

    // Dispose of snow set geometries and materials
    snowSets.forEach(snow => {
        snow.geometry.dispose();
        snow.material.dispose();
        scene.remove(snow);
    });
    snowSets = [];

    // Dispose of plane geometry and material
    if (plane) {
        plane.geometry.dispose();
        plane.material.dispose();
        scene.remove(plane);
        plane = null;
    }

    // Dispose of composer and its passes
    if (composer) {
        composer.passes.forEach(pass => {
            if (pass.dispose) {
                pass.dispose();
            }
        });
        composer = null;
    }

    // Dispose of renderer
    if (renderer) {
        renderer.dispose();
        renderer.forceContextLoss();
        renderer.context = null;
        // Remove the canvas element completely
        if (renderer.domElement && renderer.domElement.parentNode) {
            renderer.domElement.parentNode.removeChild(renderer.domElement);
        }
        renderer.domElement = null;
        renderer = null;
    }

    // Clear scene
    if (scene) {
        while (scene.children.length > 0) {
            scene.remove(scene.children[0]);
        }
        scene = null;
    }

    // Clear audio resources
    if (analyser) {
        analyser = null;
    }

    if (zhe && scene) {
        scene.remove(zhe);
        if (zhe.material) {
            zhe.material.dispose();
            if (zhe.material.map) zhe.material.map.dispose();
        }
        zhe = null;
    }

    // Remove event listeners
    window.removeEventListener('resize', handleResize);
    document.removeEventListener('keydown', handleKeydown);


    const animationContainer = document.querySelectorAll('.jukebox-animation');

    animationContainer.forEach(element => {
        element.parentNode.removeChild(element); // Remove each element
    });

    const overlay = document.getElementById("overlay");
    overlay.style.display = "block";

}