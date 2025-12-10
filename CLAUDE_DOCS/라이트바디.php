<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Light Body 스마트 해독센터 (AI 상담 포함)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background-color: #e0f2fe;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .app-container {
            width: 100%;
            max-width: 450px; /* Mobile App width */
            background: linear-gradient(180deg, #E0F7FA 0%, #E3F2FD 50%, #BBDEFB 100%);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            color: #1e3a8a;
            border-radius: 20px;
            min-height: 90vh;
        }
        
        .benefit-circle {
            background: linear-gradient(135deg, #4DD0E1 0%, #0288D1 100%);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            aspect-ratio: 1;
            box-shadow: 0 4px 6px rgba(0,136,209,0.3), inset 0 2px 5px rgba(255,255,255,0.4);
            color: white;
            transition: transform 0.2s;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        .benefit-circle:hover {
            transform: scale(1.05);
        }
        .benefit-circle::after {
            content: '';
            position: absolute;
            top: 5%;
            left: 15%;
            width: 70%;
            height: 40%;
            background: linear-gradient(180deg, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0) 100%);
            border-radius: 50%;
        }

        .image-container {
            position: relative;
            width: 100%;
            height: 280px;
            margin: 1rem auto;
        }
        .main-image-circle {
            width: 220px;
            height: 220px;
            border-radius: 50%;
            overflow: hidden;
            border: 6px solid #ffffff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            background-color: #fff;
        }
        .sub-image-circle {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #0288D1;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            position: absolute;
            bottom: 0;
            left: 10%;
            z-index: 15;
            background-color: #fff;
        }

        .bg-grid {
            background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px);
            background-size: 40px 40px;
            background-position: center center;
            opacity: 0.2;
        }
        
        /* AI Feature Styles */
        .ai-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            margin: 1rem;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
            border: 1px solid #BBDEFB;
        }
        .loading-dots:after {
            content: '.';
            animation: dots 1.5s steps(5, end) infinite;
        }
        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60% { content: '...'; }
            80%, 100% { content: ''; }
        }
    </style>
</head>
<body>

    <div class="app-container">
        
        <div class="absolute inset-0 bg-grid pointer-events-none"></div>

        <!-- Header -->
        <div class="px-6 pt-8 pb-2 z-10 text-center">
            <div class="bg-[#0288D1] text-white py-2 px-4 rounded-full shadow-md mb-4 inline-block">
                <h2 class="text-sm font-bold tracking-wider">HEALTH & DETOX CENTER</h2>
            </div>
            <h1 class="text-5xl font-black text-[#01579B] mb-1 tracking-tight" style="text-shadow: 2px 2px 0px rgba(255,255,255,0.5);">Light Body</h1>
            <div class="flex justify-center items-center gap-2">
                <span class="text-2xl font-bold text-[#0288D1]">해독센터</span>
                <span class="bg-[#01579B] text-white text-sm font-bold px-2 py-0