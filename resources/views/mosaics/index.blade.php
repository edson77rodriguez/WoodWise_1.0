<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>Ortomosaicos UAV</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f5;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        h1 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 16px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        select,
        input,
        button {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
        }

        button {
            margin-top: 20px;
            cursor: pointer;
        }

        progress {
            width: 100%;
            height: 24px;
            margin-top: 20px;
        }

        .status {
            margin-top: 10px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .uploaded {
            color: #177245;
        }

        .uploading {
            color: #b26a00;
        }

    </style>

</head>


<body>

<div class="container">


    <div class="card">

        <h1>
            Ortomosaicos UAV
        </h1>

        <p>
            Carga directa de GeoTIFF a Cloudflare R2.
        </p>


        <label>
            Proyecto
        </label>

        <select id="project">

            @foreach($projects as $project)

                <option value="{{ $project->id }}">

                    {{ $project->name }}

                </option>

            @endforeach

        </select>


        <label>
            Ortomosaico GeoTIFF
        </label>

        <input
            type="file"
            id="mosaicFile"
            accept=".tif,.tiff,image/tiff"
        >


        <button id="uploadButton">

            Subir ortomosaico

        </button>


        <progress
            id="uploadProgress"
            value="0"
            max="100"
            hidden
        ></progress>


        <div
            id="uploadStatus"
            class="status"
        ></div>

    </div>



    <div class="card">

        <h2>
            Ortomosaicos registrados
        </h2>


        <table>

            <thead>

                <tr>

                    <th>
                        Archivo
                    </th>

                    <th>
                        Proyecto
                    </th>

                    <th>
                        Tamaño
                    </th>

                    <th>
                        Estado
                    </th>

                </tr>

            </thead>


            <tbody>

            @forelse($mosaics as $mosaic)

                <tr>

                    <td>
                        {{ $mosaic->original_name }}
                    </td>

                    <td>
                        {{ $mosaic->project->name }}
                    </td>

                    <td>

                        @if($mosaic->size_bytes)

                            {{
                                number_format(
                                    $mosaic->size_bytes
                                    / 1024
                                    / 1024,
                                    2
                                )
                            }}
                            MB

                        @else

                            -

                        @endif

                    </td>

                    <td>

                        <span class="{{ $mosaic->status }}">

                            {{ $mosaic->status }}

                        </span>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="4">

                        Todavía no hay ortomosaicos.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>


</div>



<script>

const csrf =
    document
        .querySelector(
            'meta[name="csrf-token"]'
        )
        .content;


const button =
    document.getElementById(
        'uploadButton'
    );


const progress =
    document.getElementById(
        'uploadProgress'
    );


const statusBox =
    document.getElementById(
        'uploadStatus'
    );



button.addEventListener(
    'click',
    async () => {

        const fileInput =
            document.getElementById(
                'mosaicFile'
            );

        const project =
            document.getElementById(
                'project'
            );


        if (!fileInput.files.length) {

            statusBox.textContent =
                'Selecciona un archivo .tif o .tiff';

            return;
        }


        const file =
            fileInput.files[0];


        try {

            button.disabled = true;

            progress.hidden = false;

            progress.value = 0;


            statusBox.textContent =
                'Preparando subida...';


            /*
            |--------------------------------------------------------------------------
            | 1. Solicitar URL firmada
            |--------------------------------------------------------------------------
            */

            const presignResponse =
                await fetch(
                    '{{ route('mosaics.presign') }}',
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrf,

                            'Accept':
                                'application/json'
                        },

                        body: JSON.stringify({

                            project_id:
                                project.value,

                            filename:
                                file.name,

                            size_bytes:
                                file.size,

                            mime_type:
                                file.type
                                || 'image/tiff'
                        })
                    }
                );


            const presign =
                await presignResponse.json();


            if (!presignResponse.ok) {

                throw new Error(
                    presign.message
                    || 'No se pudo preparar la subida.'
                );
            }


            statusBox.textContent =
                'Subiendo directamente a R2...';


            /*
            |--------------------------------------------------------------------------
            | 2. Subir directamente navegador -> R2
            |--------------------------------------------------------------------------
            */

            await uploadToR2(
                file,
                presign.upload_url,
                presign.headers
            );


            /*
            |--------------------------------------------------------------------------
            | 3. Confirmar con Laravel
            |--------------------------------------------------------------------------
            */

            statusBox.textContent =
                'Verificando archivo...';


            const completeResponse =
                await fetch(
                    '{{ route('mosaics.complete') }}',
                    {
                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrf,

                            'Accept':
                                'application/json'
                        },

                        body: JSON.stringify({

                            mosaic_uuid:
                                presign.mosaic_uuid
                        })
                    }
                );


            const complete =
                await completeResponse.json();


            if (!completeResponse.ok) {

                throw new Error(
                    complete.message
                    || 'No se pudo confirmar la subida.'
                );
            }


            progress.value = 100;


            statusBox.textContent =
                'Ortomosaico almacenado correctamente.';


            setTimeout(() => {

                window.location.reload();

            }, 1200);


        } catch (error) {

            console.error(error);

            statusBox.textContent =
                'Error: ' + error.message;


        } finally {

            button.disabled = false;

        }

    }
);



function uploadToR2(
    file,
    url,
    signedHeaders
) {

    return new Promise(
        (resolve, reject) => {

            const xhr =
                new XMLHttpRequest();


            xhr.open(
                'PUT',
                url,
                true
            );


            /*
            |--------------------------------------------------------------------------
            | Headers generados por Laravel / S3
            |--------------------------------------------------------------------------
            */

            if (signedHeaders) {

                Object.entries(
                    signedHeaders
                ).forEach(
                    ([key, value]) => {

                        /*
                         * El navegador controla Host.
                         */

                        if (
                            key.toLowerCase()
                            !== 'host'
                        ) {

                            xhr.setRequestHeader(
                                key,
                                value
                            );
                        }

                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Progreso
            |--------------------------------------------------------------------------
            */

            xhr.upload.onprogress =
                function (event) {

                    if (
                        event.lengthComputable
                    ) {

                        const percent =
                            Math.round(
                                (
                                    event.loaded
                                    / event.total
                                )
                                * 100
                            );

                        progress.value =
                            percent;

                        statusBox.textContent =
                            'Subiendo: '
                            + percent
                            + '%';
                    }
                };


            xhr.onload =
                function () {

                    if (
                        xhr.status >= 200
                        &&
                        xhr.status < 300
                    ) {

                        resolve();

                    } else {

                        reject(
                            new Error(
                                'R2 respondió HTTP '
                                + xhr.status
                            )
                        );
                    }
                };


            xhr.onerror =
                function () {

                    reject(
                        new Error(
                            'Error de red o CORS durante la subida.'
                        )
                    );
                };


            xhr.send(file);

        }
    );
}

</script>


</body>

</html>