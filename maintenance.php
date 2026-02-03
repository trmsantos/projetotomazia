<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenção - Bar da Tomazia</title>
    <link rel="icon" href="img/tomazia.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #5D1F3A;
            color: #f0f0f0;
            font-family: 'Montserrat', Arial, sans-serif;
            min-height: 100vh;
            overflow: hidden;
        }
        .video-overlay {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(93,31,58,0.8); z-index: 1;
        }
        .navbar {
            background-color: rgba(93,31,58,0.95)!important;
            border-bottom: 1px solid rgba(212,175,55,0.2);
            z-index: 2;
        }
        .navbar-brand img { height: 80px; }
        .maintenance-container {
            position: relative;
            z-index: 2;
            max-width: 500px;
            margin: 120px auto 0 auto;
            background: rgba(61,15,36,0.95);
            padding: 40px 32px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            border: 1px solid rgba(212,175,55,0.3);
            text-align: center;
        }
        .maintenance-container h1 {
            color: #D4AF37;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .maintenance-container p {
            color: #cccccc;
            margin-bottom: 30px;
            font-size: 1.2rem;
        }
        .maintenance-gear-canvas {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0;
        }
    </style>
</head>
<body>
    <canvas class="maintenance-gear-canvas"></canvas>
    <div class="video-overlay"></div>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#"><img src="/img/tomazia.png" alt="Tomazia"></a>
    </nav>
    <div class="container">
        <div class="maintenance-container">
            <h1>Em Manutenção</h1>
            <p>O Bar da Tomazia está temporariamente em manutenção.<br>
               Por favor, volte mais tarde.<br>
               <small style="color:#D4AF37;">Estamos a preparar novidades para si!</small></p>
        </div>
    </div>
    <script>
        // Engrenagens animadas em canvas
        const canvas = document.querySelector('.maintenance-gear-canvas');
        const ctx = canvas.getContext('2d');
        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resize);
        resize();

        // Gear data
        const gears = [
            {x: 180, y: 260, r: 55, teeth: 12, speed: 0.012, color: "#D4AF37", angle: 0},
            {x: 270, y: 340, r: 32, teeth: 8, speed: -0.02, color: "#a0a0a0", angle: 0},
            {x: 380, y: 220, r: 38, teeth: 10, speed: 0.017, color: "#f0f0f0", angle: 0},
            {x: 600, y: 420, r: 60, teeth: 14, speed: -0.015, color: "#D4AF37", angle: 0},
            {x: 700, y: 200, r: 42, teeth: 8, speed: 0.021, color: "#a0a0a0", angle: 0}
        ];
        function drawGear(g) {
            ctx.save();
            ctx.translate(g.x, g.y);
            ctx.rotate(g.angle);
            ctx.beginPath();
            for (let i=0; i<g.teeth; i++) {
                let a = (i/g.teeth)*2*Math.PI;
                let r1 = g.r;
                let r2 = g.r + 12;
                ctx.lineTo(Math.cos(a)*r2, Math.sin(a)*r2);
                ctx.lineTo(Math.cos(a+0.13)*r1, Math.sin(a+0.13)*r1);
            }
            ctx.closePath();
            ctx.fillStyle = g.color+"88";
            ctx.fill();
            ctx.beginPath();
            ctx.arc(0,0,g.r-10,0,2*Math.PI);
            ctx.fillStyle = g.color;
            ctx.fill();
            ctx.restore();
        }
        function animate() {
            ctx.clearRect(0,0,canvas.width,canvas.height);
            gears.forEach(g=>{
                drawGear(g);
                g.angle += g.speed;
            });
            requestAnimationFrame(animate);
        }
        animate();
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>