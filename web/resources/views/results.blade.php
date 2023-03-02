<div id="result-container-collapse" class="collapse show">
    <div id="result-container" class="hidden">
        <div id="loading-container">    
            <div class="spinner-border"></div>
            <h5>Loading...</h5>
        </div>
        <h4>Scan Results For <span id="scan-title"></span></h4>
        <div class="d-flex flex-row">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pages Scanned</h5>
                    <p class="card-text text-center fw-bold" id="scan-page-count"></p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Unique Images</h5>
                    <p class="card-text text-center fw-bold" id="scan-image-count"></p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Unique Internal Links</h5>
                    <p class="card-text text-center fw-bold" id="scan-internal-link-count"></p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Unique External Links</h5>
                    <p class="card-text text-center fw-bold" id="scan-external-link-count"></p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Average Page Load</h5>
                    <p class="card-text text-center fw-bold" id="scan-page-time"></p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Average Word Count</h5>
                    <p class="card-text text-center fw-bold" id="scan-word-count"></p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Average Title Length</h5>
                    <p class="card-text text-center fw-bold" id="scan-title-length"></p>
                </div>
            </div>
        </div>
        <h4 class="mt-4">Pages Crawled</h4>
        <table id="scan-table" class="table table-striped">
            <thead id="scan-table-head"><tr><td>Page URL</td><td>Response Code</tr></thead>
            <tbody id="scan-table-body"><tr><td><span class="placeholder col-6"></span></td><td><span class="placeholder col-2"></span></td></tr></tbody>
</table>
        </table>
    </div>
</div>