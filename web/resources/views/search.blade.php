<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PHP Parser - Search</title>

    <link href="/css/app.css" rel="stylesheet"></link>
</head>

<body class="d-flex flex-column container text-center">
    <div class="flex-grow-1"></div>

        @include('results')
    <div class="row my-5">
        <h1>
            PHP Web Crawler
        </h1>
    </div>
    <div class="row">
        <h6 class="text-muted">
            Basic crawling utility, described <a href="https://tharba.kim/laravel-web-crawler/">over here</a>.
        </h6>
    </div>
    <div class="input-group">
        <input class="form-control" type="text" id="search-url" name="search-url" placeholder="Initial URL to Crawl"></input>
        <input class="form-control" type="text" id="search-url-depth" name="search-url-depth" placeholder="Depth"></input>
        <button class="btn btn-success" type="submit" id="search-submit" name="search-submit">Go</button>
    </div>
    <div class="row text-start">
        <p>
            <small class="text-muted">
                <strong>Initial URL to Crawl:</strong> Must be a valid URL (including https://), and you must have permission to crawl it.
            </small>
        </p>
        <p>
            <small class="text-muted">
                <strong>Depth:</strong> Is the number of additional pages to crawl, based on URLs found in the source of the initial URL. Note that only URLs with the same domain name (including subdomain) will be followed.
            </small>
        </p>
    </div>

    <div class="flex-grow-1"></div>

    <script src="/js/app.js"></script>
</body>

</html>