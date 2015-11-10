<?php

/**
 * Class View
 *
 * Represenents the presentation layer.
 * Prepares the the basket/order templates for presentation on the website.
 * 
 * $Id: view.php 32282 2015-09-16 11:06:43Z sseiz $
 */
class View
{
	private $path = '/../templates';
	private $template = 'default';

	/**
	 * Coantains embedded variables from the controller.
	 * 
	 */
	private $_ = array();

	/**
	 * Assigns a value to a specific key.
	 *
	 * @param String $key
	 * @param String $value
	 */
	public function Assign($key, $value)
	{
		$this->_[$key] = $value;

		/*
		// Wenn Array, noch zusätzlich speichern.
		if(is_array($value))
		{
			$this->_ = array_merge($value, $this->_);
		}
		*/
	}
	
	/**
	 * Sets the template name
	 *
	 * @param String $template Name of the template.
	 */
	public function SetTemplate($template = 'default')
	{
		$this->template = $template;
	}

	/**
	 * Load template-file and return it.
	 *
	 * @param string $tpl Name of the template file.
	 * @return string Output.
	 */
	public function LoadTemplate()
	{
		SetTimeZone();

		$tpl = $this->template;
		// Path to check if the template exists.
		$file =  realpath(  dirname( __FILE__ ) . $this->path ) . DIRECTORY_SEPARATOR . $tpl . '.php';
		$exists = file_exists($file);

		if ($exists)
		{
			// Buffer the output from now on.
			ob_start();

			// Embed the template and save in $output
			include $file;
			$output = ob_get_contents();

			// Clean the buffer so that nothing is put out on the website.
			ob_end_clean();
			
			return $output;
		}
		else 
		{
			return "Error: Could not find template '$file'.";
		}
	}
}
?>