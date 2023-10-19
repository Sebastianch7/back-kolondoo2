<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-12 my-2">
                <h2>Services</h2>
            </div>
            <div class="col-10 align-self-center mx-auto mb-5">
                <ul class="list-group">
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getOperadoras"><b>GET: </b>getOperadoras</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getComercializadoras"><b>GET: </b>getComercializadoras</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getComercializadorasGas"><b>GET: </b>getComercializadorasGas</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getOperadorasFibra"><b>GET: </b>getOperadorasFibra</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getTarifasMovil"><b>GET: </b>getTarifasMovil</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/filterMovil"><b>GET: </b>filterMovil</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getDetailOfferMovil/1"><b>GET: </b>getDetailOfferMovil/{id}</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getExtraOfferMovil"><b>GET: </b>getExtraOfferMovil</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getTarifasLuz"><b>GET: </b>getTarifasLuz</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getExtraOfferLuz"><b>GET: </b>getExtraOfferLuz</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getDetailOfferLuz/1"><b>GET: </b>getDetailOfferLuz/{id}</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getTarifasGas"><b>GET: </b>getTarifasGas</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getExtraOfferGas"><b>GET: </b>getExtraOfferGas</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getDetailOfferGas/1"><b>GET: </b>getDetailOfferGas/{id}</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getTarifasFibra"><b>GET: </b>getTarifasFibra</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/filterFibra"><b>GET: </b>filterFibra</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getExtraOfferFibra"><b>GET: </b>getExtraOfferFibra</a>
                    <a class="list-group-item list-group-item-action" aria-current="true" target="_blank" href="api/getDetailOfferFibra/1"><b>GET: </b>getDetailOfferFibra/{id}</a>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>