<?php

namespace niklasravnsborg\LaravelPdf;

interface PdfInterface
{
    /**
     * Encrypts and sets the PDF document permissions
     *
     * @param array $permission Permissons e.g.: ['copy', 'print']
     * @param string $userPassword User password
     * @param string $ownerPassword Owner password
     *
     * @return mixed
     */
    public function setProtection(array $permission, string $userPassword = '', string $ownerPassword = '');

    /**
     * Output the PDF as a string.
     *
     * @return string The rendered PDF as string
     */
    public function output();

    /**
     * Save the PDF to a file
     *
     * @param mixed
     *
     * @return void
     */
    public function save(string $filename);

    /**
     * Make the PDF downloadable by the user
     *
     * @param string $filename
     *
     * @return void
     */
    public function download(string $filename);

    /**
     * Return a response with the PDF to show in the browser
     *
     * @param string $filename
     *
     * @return void
     */
    public function stream(string $filename);
}
