<?php

namespace niklasravnsborg\LaravelPdf\Wrapper;

use File;
use niklasravnsborg\LaravelPdf\Pdf;
use niklasravnsborg\LaravelPdf\PdfInterface;
use View;

class PdfWrapper implements PdfWrapperInterface
{
    /**
     * Loads a HTML string
     *
     * @param string $html
     * @param array $config
     *
     * @return \niklasravnsborg\LaravelPdf\PdfInterface
     */
    public function loadHTML(string $html, array $config = []): PdfInterface
    {
        return new Pdf($html, $config);
    }

    /**
     * Loads a HTML file
     *
     * @param string $file
     * @param array $config
     *
     * @return \niklasravnsborg\LaravelPdf\PdfInterface
     */
    public function loadFile(string $file, array $config = []): PdfInterface
    {
        return new Pdf(File::get($file), $config);
    }

    /**
     * Loads a View and convert to HTML
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @param array $config
     *
     * @return \niklasravnsborg\LaravelPdf\PdfInterface
     */
    public function loadView(string $view, array $data = [], array $mergeData = [], array $config = []): PdfInterface
    {
        return new Pdf(
            View::make($view, $data, $mergeData)->render(),
            $config
        );
    }
}
