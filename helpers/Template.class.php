<?php
class Template {
	protected $templatePath;
	protected $data = array(); 
	
	public function __construct($template) {
		if( ! is_file($template)) 
            die('The template "' . $template . '" is invalid.'); 
             
        $this->templatePath = $template; 
	}
	
	public function __toString() 
    { 
        return $this->render() . "\n"; 
    } 
	
	public function __set($key, $value) 
    { 
        $this->data[$key] = $value; 
    } 
	
	public function render() {
		ob_start();
		
		extract($this->data, EXTR_SKIP);
		
		require_once($this->templatePath); 
		
		$output = ob_get_clean(); 
        
		// $output = preg_replace("/\n\r|\r\n|\n|\r/","",$output);
		// $output = preg_replace("/\t/","",$output);
		
        echo $output;
	}
	
}
?>