<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'UAV Forest AI')
    </title>


    <style>

        :root {

            --bg: #f4f7f6;

            --surface: #ffffff;
            --surface-soft: #f8faf9;

            --text: #17211d;
            --text-secondary: #64706a;

            --border: #dfe7e3;

            --primary: #176b4d;
            --primary-dark: #105239;
            --primary-soft: #e8f4ef;

            --blue: #2463a7;
            --blue-soft: #eaf2fb;

            --warning: #b7791f;
            --warning-soft: #fff7e8;

            --danger: #b42318;
            --danger-soft: #fff0ee;

            --success: #16794b;
            --success-soft: #ecf8f2;

            --shadow:
                0 10px 35px rgba(20, 40, 30, 0.08);

            --radius: 16px;

        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Inter,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;

            background: var(--bg);

            color: var(--text);

        }


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        .topbar {

            background:
                linear-gradient(
                    135deg,
                    #103d2e,
                    #176b4d
                );

            color: white;

            padding: 20px 30px;

            box-shadow:
                0 4px 20px rgba(0,0,0,.12);

        }


        .topbar-inner {

            max-width: 1400px;

            margin: auto;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

        }


        .brand {

            display: flex;

            align-items: center;

            gap: 14px;

        }


        .brand-icon {

            width: 44px;
            height: 44px;

            display: grid;

            place-items: center;

            border-radius: 12px;

            background:
                rgba(255,255,255,.14);

            font-size: 22px;

        }


        .brand-title {

            font-size: 19px;

            font-weight: 700;

            margin: 0;

        }


        .brand-subtitle {

            font-size: 13px;

            opacity: .78;

            margin-top: 2px;

        }


        /*
        |--------------------------------------------------------------------------
        | Layout
        |--------------------------------------------------------------------------
        */

        .page {

            max-width: 1400px;

            margin: auto;

            padding: 32px;

        }


        .card {

            background: var(--surface);

            border:
                1px solid var(--border);

            border-radius:
                var(--radius);

            box-shadow:
                var(--shadow);

        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        .status {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding:
                7px 12px;

            border-radius:
                999px;

            font-size:
                13px;

            font-weight:
                600;

        }


        .status-dot {

            width: 8px;

            height: 8px;

            border-radius: 50%;

        }


        .status-online {

            background:
                rgba(255,255,255,.14);

        }


        .status-online .status-dot {

            background: #72e6a8;

            box-shadow:
                0 0 0 4px
                rgba(114,230,168,.12);

        }


        .status-offline {

            background:
                rgba(255,255,255,.12);

        }


        .status-offline .status-dot {

            background: #ff9c9c;

        }


        /*
        |--------------------------------------------------------------------------
        | Alerts
        |--------------------------------------------------------------------------
        */

        .alert {

            padding: 16px 18px;

            border-radius: 12px;

            margin-bottom: 22px;

            font-size: 14px;

        }


        .alert-error {

            background:
                var(--danger-soft);

            color:
                var(--danger);

            border:
                1px solid #f6cbc7;

        }


        .alert-warning {

            background:
                var(--warning-soft);

            color:
                #805816;

            border:
                1px solid #f3dda8;

        }


        /*
        |--------------------------------------------------------------------------
        | Forms
        |--------------------------------------------------------------------------
        */

        label {

            display: block;

            font-weight: 600;

            font-size: 14px;

            margin-bottom: 8px;

        }


        input,
        select {

            width: 100%;

            border:
                1px solid var(--border);

            border-radius:
                10px;

            padding:
                11px 13px;

            font-size:
                14px;

            background:
                white;

            color:
                var(--text);

            transition:
                .2s;

        }


        input:focus,
        select:focus {

            outline: none;

            border-color:
                var(--primary);

            box-shadow:
                0 0 0 3px
                rgba(23,107,77,.1);

        }


        .field {

            margin-bottom:
                20px;

        }


        .help {

            color:
                var(--text-secondary);

            font-size:
                12px;

            margin-top:
                7px;

            line-height:
                1.45;

        }


        /*
        |--------------------------------------------------------------------------
        | Buttons
        |--------------------------------------------------------------------------
        */

        .btn {

            border: 0;

            border-radius:
                10px;

            padding:
                12px 20px;

            font-size:
                14px;

            font-weight:
                650;

            cursor:
                pointer;

            transition:
                .2s;

        }


        .btn-primary {

            background:
                var(--primary);

            color:
                white;

        }


        .btn-primary:hover {

            background:
                var(--primary-dark);

            transform:
                translateY(-1px);

        }


        .btn:disabled {

            opacity:
                .65;

            cursor:
                wait;

        }


        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media(max-width: 800px) {

            .page {

                padding:
                    18px;

            }

            .topbar {

                padding:
                    16px 18px;

            }

            .topbar-inner {

                align-items:
                    flex-start;

                flex-direction:
                    column;

            }

        }

    </style>


    @stack('styles')

</head>


<body>


<header class="topbar">

    <div class="topbar-inner">

        <div class="brand">

            <div class="brand-icon">
                🌲
            </div>

            <div>

                <div class="brand-title">
                    UAV Forest AI
                </div>

                <div class="brand-subtitle">
                    Análisis inteligente de copas arbóreas
                </div>

            </div>

        </div>


        @isset($apiStatus)

            @if(
                ($apiStatus['status'] ?? 'offline')
                === 'ok'
            )

                <div class="status status-online">

                    <span class="status-dot"></span>

                    API IA conectada

                    @if(
                        !empty(
                            $apiStatus['version']
                        )
                    )

                        · v{{ $apiStatus['version'] }}

                    @endif

                </div>

            @else

                <div class="status status-offline">

                    <span class="status-dot"></span>

                    API IA desconectada

                </div>

            @endif

        @endisset

    </div>

</header>


<main class="page">

    @yield('content')

</main>


@stack('scripts')


</body>
</html>