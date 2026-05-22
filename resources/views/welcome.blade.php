<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISMA-RT</title>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Inter', sans-serif;
            background: radial-gradient(circle at top right, #17394a 0%, #0b1d28 40%, #07131d 100%);
            color:white;
            min-height:100vh;
            overflow-x:hidden;
            position:relative;
        }

        /* ORNAMEN 4 POJOK */
        .ornament{
            position:absolute;
            width:180px;
            height:180px;
            border:2px solid rgba(216,180,106,0.2);
            border-radius:50%;
            z-index:1;
        }

        .ornament::before{
            content:"";
            position:absolute;
            inset:20px;
            border:2px solid rgba(216,180,106,0.15);
            border-radius:50%;
        }

        .ornament::after{
            content:"";
            position:absolute;
            inset:40px;
            border:2px solid rgba(216,180,106,0.1);
            border-radius:50%;
        }

        .top-left{
            top:-60px;
            left:-60px;
        }

        .top-right{
            top:-60px;
            right:-60px;
        }

        .bottom-left{
            bottom:-60px;
            left:-60px;
        }

        .bottom-right{
            bottom:-60px;
            right:-60px;
        }

        /* NAVBAR */
        .navbar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:25px 8%;
            position:relative;
            z-index:10;
        }

        .logo{
            display:flex;
            align-items:center;
            gap:10px;
            font-weight:700;
            font-size:22px;
            color:#d8b46a;
        }

        .nav-links{
            display:flex;
            align-items:center;
            gap:20px;
        }

        .nav-links a{
            color:white;
            text-decoration:none;
            font-size:15px;
            transition:0.3s;
        }

        .nav-links a:hover{
            color:#d8b46a;
        }

        .btn-login{
            padding:10px 24px;
            border-radius:30px;
            border:none;
            background:linear-gradient(135deg,#d8b46a,#8c6b2d);
            color:white;
            font-weight:600;
            box-shadow:0 0 15px rgba(216,180,106,0.5);
        }

        .btn-register{
            padding:10px 24px;
            border-radius:30px;
            border:1px solid #d8b46a;
            color:#d8b46a;
            background:transparent;
            font-weight:600;
        }

        /* HERO */
        .hero{
            display:flex;
            justify-content:space-between;
            align-items:center;
            min-height:85vh;
            padding:0 8%;
            gap:50px;
            position:relative;
            z-index:10;
        }

        .hero-text{
            flex:1;
        }

        .hero-text h1{
            font-family:'Cinzel', serif;
            font-size:4rem;
            line-height:1.2;
            color:#d8b46a;
            margin-bottom:20px;
        }

        .hero-text h1 span{
            color:white;
        }

        .hero-text p{
            color:#d4d4d4;
            max-width:500px;
            line-height:1.7;
            margin-bottom:30px;
        }

        .hero-buttons{
            display:flex;
            gap:15px;
            margin-bottom:25px;
        }

        .btn-primary{
            background:linear-gradient(135deg,#7b3ff2,#5f2eea);
            color:white;
            padding:14px 28px;
            border-radius:30px;
            text-decoration:none;
            font-weight:600;
        }

        .btn-outline{
            border:1px solid #d8b46a;
            color:#d8b46a;
            padding:14px 28px;
            border-radius:30px;
            text-decoration:none;
            font-weight:600;
        }

        .trust{
            display:flex;
            gap:25px;
            flex-wrap:wrap;
            color:#d8b46a;
            font-size:14px;
        }

        /* CARD HERO */
        .hero-card{
            flex:1;
            display:flex;
            justify-content:center;
        }

        .card-box{
            width:400px;
            height:400px;
            border:1px solid rgba(216,180,106,0.5);
            border-radius:20px;
            background:rgba(255,255,255,0.03);
            backdrop-filter:blur(10px);
            box-shadow:0 0 30px rgba(216,180,106,0.15);
            overflow:hidden;
        }

        .card-box img{
            width:100%;
            height:100%;
            object-fit:cover;
            border-radius:20px;
        }

        @media(max-width:992px){
            .hero{
                flex-direction:column;
                text-align:center;
                padding-top:40px;
            }

            .hero-text h1{
                font-size:2.5rem;
            }

            .hero-buttons,
            .trust{
                justify-content:center;
            }

            .card-box{
                width:300px;
                height:300px;
            }

            .nav-links{
                flex-wrap:wrap;
                justify-content:center;
            }
        }
    </style>
</head>
<body>

    <!-- ORNAMEN -->
    <div class="ornament top-left"></div>
    <div class="ornament top-right"></div>
    <div class="ornament bottom-left"></div>
    <div class="ornament bottom-right"></div>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-mosque"></i> RISMA-RT
        </div>

        <div class="nav-links">
            <a href="#">Fitur</a>
            <a href="#">Tentang</a>
            <a href="{{ route('login') }}" class="btn-login">Log Masuk</a>
            <a href="{{ route('register') }}" class="btn-register">Daftar</a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">

        <div class="hero-text">
            <h1>
                Kelola Iuran RT <br>
                <span>Lebih Mudah, Berkah</span> <br>
                & Transparan
            </h1>

            <p>
                RISMA-RT adalah platform digital yang membantu mengelola iuran warga
                dengan prinsip syariah. Praktis, aman, dan mudah digunakan untuk kebersamaan warga RT.
            </p>

            <div class="hero-buttons">
                <a href="{{ route('register') }}" class="btn-primary">
                    <i class="fas fa-home"></i> Mulai Berbagi Sekarang
                </a>

                <a href="#" class="btn-outline">
                    <i class="fas fa-play"></i> Lihat Program & Fitur
                </a>
            </div>

            <div class="trust">
                <span><i class="fas fa-shield-alt"></i> Amanah</span>
                <span><i class="fas fa-eye"></i> Transparan</span>
                <span><i class="fas fa-mosque"></i> Sesuai Syariah</span>
            </div>
        </div>

        <!-- GAMBAR HERO -->
        <div class="hero-card">
            <div class="card-box">
                <img src="{{ asset('images/banner.png') }}" alt="Banner">
            </div>
        </div>

    </section>

</body>
</html>