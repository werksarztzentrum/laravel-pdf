<?php

namespace niklasravnsborg\LaravelPdf;

use Config;
use Mpdf;
use Mpdf\Output\Destination;

/**
 * Laravel PDF: mPDF wrapper for Laravel 5
 *
 * @package laravel-pdf
 * @author Niklas Ravnsborg-Gjertsen
 */
class Pdf implements PdfInterface
{
	/**
	 * @var array|mixed
	 */
	protected $config = [];

	/**
	 * @param string $html
	 * @param array $config
	 *
	 * @throws \Mpdf\MpdfException
	 */
	public function __construct(string $html = '', array $config = [])
	{
		$this->config = $config;

		// @see https://mpdf.github.io/reference/mpdf-functions/construct.html
		$mpdf_config = [
			'mode'              => $this->getConfig('mode'),              // Mode of the document.
			'format'            => $this->getConfig('format'),            // Can be specified either as a pre-defined page size, or as an array of width and height in millimetres
			'default_font_size' => $this->getConfig('default_font_size'), // Sets the default document font size in points (pt).
			'default_font'      => $this->getConfig('default_font'),      // Sets the default font-family for the new document.
			'margin_left'       => $this->getConfig('margin_left'),       // Set the page margins for the new document.
			'margin_right'      => $this->getConfig('margin_right'),      // Set the page margins for the new document.
			'margin_top'        => $this->getConfig('margin_top'),        // Set the page margins for the new document.
			'margin_bottom'     => $this->getConfig('margin_bottom'),     // Set the page margins for the new document.
			'margin_header'     => $this->getConfig('margin_header'),     // Set the page margins for the new document.
			'margin_footer'     => $this->getConfig('margin_footer'),     // Set the page margins for the new document.
			'orientation'       => $this->getConfig('orientation'),       // This attribute specifies the default page orientation of the new document if format is defined as an array. This value will be ignored if format is a string value.
			'tempDir'           => $this->getConfig('tempDir'),           // Temporary directory
		];

		$defaultCssFile = $this->getConfig('defaultCssFile');             // Set Default Style Sheet
		if (file_exists($defaultCssFile)) {
			$mpdf_config['defaultCssFile'] = $defaultCssFile;
		}

		// Handle custom fonts
		$mpdf_config = $this->addCustomFontsConfig($mpdf_config);

		$this->mpdf = new Mpdf\Mpdf($mpdf_config);

		// If you want to change your document title,
		// please use the <title> tag.
		$this->mpdf->SetTitle('Document');

		$this->mpdf->SetAuthor        ( $this->getConfig('author') );
		$this->mpdf->SetCreator       ( $this->getConfig('creator') );
		$this->mpdf->SetSubject       ( $this->getConfig('subject') );
		$this->mpdf->SetKeywords      ( $this->getConfig('keywords') );
		$this->mpdf->SetDisplayMode   ( $this->getConfig('display_mode') );

		if (!empty($this->getConfig('pdf_a'))) {
			$this->mpdf->PDFA = $this->getConfig('pdf_a');           // Set the flag whether you want to use the pdfA-1b format
			$this->mpdf->PDFAauto = $this->getConfig('pdf_a_auto');  // Overrides warnings making changes when possible to force PDFA1-b compliance;
		}

		if (!empty($this->getConfig('icc_profile_path'))) {
			$this->mpdf->ICCProfile = $this->getConfig('icc_profile_path'); // Specify ICC colour profile
		}

		if (isset($this->config['instanceConfigurator']) && is_callable(($this->config['instanceConfigurator']))) {
			$this->config['instanceConfigurator']($this->mpdf);
		}

		$this->mpdf->WriteHTML($html);
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function getConfig(string $key)
	{
		if (isset($this->config[$key])) {
			return $this->config[$key];
		}

		return Config::get('pdf.' . $key);
	}

	/**
	 * @param array $mpdfConfig
	 *
	 * @return array
	 */
	protected function addCustomFontsConfig(array $mpdfConfig): array
	{
		if (!Config::has('pdf.font_path') || !Config::has('pdf.font_data')) {
			return $mpdfConfig;
		}

		// Get default font configuration
		$fontDirs = (new Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'];
		$fontData = (new Mpdf\Config\FontVariables())->getDefaults()['fontdata'];

		// Merge default with custom configuration
		$mpdfConfig['fontDir'] = array_merge($fontDirs, [Config::get('pdf.font_path')]);
		$mpdfConfig['fontdata'] = array_merge($fontData, Config::get('pdf.font_data'));

		return $mpdfConfig;
	}

	/**
	 * Encrypts and sets the PDF document permissions
	 *
	 * @param array $permission Permissons e.g.: ['copy', 'print']
	 * @param string $userPassword User password
	 * @param string $ownerPassword Owner password
	 *
	 * @return mixed
	 */
	public function setProtection(array $permission, string $userPassword = '', string $ownerPassword = '')
	{
		if (func_get_args()[2] === NULL) {
			$ownerPassword = bin2hex(openssl_random_pseudo_bytes(8));
		};

		return $this->mpdf->SetProtection(
			$permission,
			$userPassword,
			$ownerPassword
		);
	}

	/**
	 * Output the PDF as a string.
	 *
	 * @return string The rendered PDF as string
	 */
	public function output()
	{
		return $this->mpdf->Output('', Destination::STRING_RETURN);
	}

	/**
	 * Save the PDF to a file
	 *
	 * @param $filename
	 *
	 * @return void
	 */
	public function save(string $filename)
	{
		return $this->mpdf->Output($filename, Destination::FILE);
	}

	/**
	 * Make the PDF downloadable by the user
	 *
	 * @param string $filename
	 *
	 * @return void
	 */
	public function download(string $filename = 'document.pdf')
	{
		return $this->mpdf->Output($filename, Destination::DOWNLOAD);
	}

	/**
	 * Return a response with the PDF to show in the browser
	 *
	 * @param string $filename
	 *
	 * @return void
	 */
	public function stream(string $filename = 'document.pdf')
	{
		return $this->mpdf->Output($filename, Destination::INLINE);
	}
}