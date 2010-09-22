<?php
/**
 * DMS Template System
 */

class DMS_System_Template
{
	protected $settings;
	protected $path;
	protected $templates = array();
	
	public function __construct( DMS_System_Settings $settings )
	{
		$this->settings = $settings;
		$this->path = 'tpl/'.$settings->site_template.'/';
	}
	
	public function load( $template_file )
	{
		if(!file_exists($this->path.$template_file)) {
			throw new DMS_Exceptions_FileNotExists();
		}
		$this->templates[] = $template_file;
	}
	
	public function render()
	{
		if($this->settings->site_enable_gzip)
			ob_start("ob_gzhandler");
		else ob_start();
		
		# Template scope
		{
			$settings = &$this->settings;
			if($settings->site_enable_user) {
				$session = DMS_User_Factory::getSession();
			}
			
			ob_start();
			foreach($this->templates as $t) {
				include $this->path.$t;
			}
			$contents = ob_get_contents();
			ob_end_clean();
			
			include $this->path.'header.php';
			
			echo $contents;
			
			include $this->path.'footer.php';
		}
		ob_end_flush();
	}
}