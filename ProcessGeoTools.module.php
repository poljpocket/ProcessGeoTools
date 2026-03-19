<?php namespace ProcessWire;

/**
 * ProcessGeoTools Module
 * 
 * Configures content for llms.txt files and saves it to the root folder.
 * 
 */

class ProcessGeoTools extends Process implements ConfigModule {

	public static function getModuleInfo() {
		return array(
			'title' => 'GEO Tools',
			'summary' => 'Configure content for llms.txt files.',
			'version' => 100,
			'author' => 'poljpocket',
			'icon' => 'id-card',
			'requires' => 'ProcessWire>=3.0.0, PHP>=8.1',
			'page' => array(
				'name' => 'geo-tools',
				'parent' => 'setup',
				'title' => 'GEO Tools',
			),
		);
	}

	/**
	 * Default configuration values
	 * 
	 */
	protected static array $defaultConfig = array(
		'llms_content' => '',
	);

	/**
	 * Construct
	 * 
	 */
	public function __construct() {
		foreach(self::$defaultConfig as $key => $value) {
			$this->set($key, $value);
		}
		parent::__construct();
	}

	/**
	 * Initialize the module and add hook
	 *
	 */
	public function init(): void {
		parent::init();
		// Hook to ensure that saving from the module config page also writes the file
		$this->addHookAfter('Modules::saveConfig', $this, 'hookSaveConfig');
	}

	/**
	 * Hook called after module configuration is saved
	 *
	 * @param HookEvent $event
	 *
	 */
	public function hookSaveConfig(HookEvent $event): void {
		$moduleName = $event->arguments(0);
		if($moduleName !== $this->className) return;

		$data = $event->arguments(1);
		if(!isset($data['llms_content'])) return;

		$this->saveFile($data['llms_content']);
	}

	/**
	 * Save the llms.txt file
	 *
	 * @param string $content
	 * @return bool
	 *
	 */
	protected function saveFile($content): bool {
		$filePath = $this->wire->config->paths->root . 'llms.txt';
		if(file_put_contents($filePath, $content) === false) {
			$this->error(sprintf($this->_('Unable to save llms.txt to %s'), $filePath));
			return false;
		} else {
			$this->message(sprintf($this->_('Successfully saved llms.txt to %s'), $filePath));
			return true;
		}
	}

	/**
	 * Main execution for the GEO Tools page (Admin > Setup > GEO Tools)
	 *
	 */
	public function ___execute() {
		/** @var InputfieldForm $form */
		$form = $this->wire->modules->get('InputfieldForm');
		$form->attr('id', 'geo_tools_form');
		$form->attr('method', 'post');
		$form->attr('action', './');

		/** @var InputfieldTextarea $f */
		$f = $this->wire->modules->get('InputfieldTextarea');
		$f->attr('name', 'llms_content');
		$f->label = $this->_('llms.txt content');
		$f->description = $this->_('Configure content for llms.txt files.');
		$f->attr('value', $this->llms_content);
		$f->attr('rows', 15);
		$form->add($f);

		/** @var InputfieldSubmit $submit */
		$submit = $this->wire->modules->get('InputfieldSubmit');
		$submit->attr('name', 'submit_save');
		$submit->attr('value', $this->_('Save'));
		$form->add($submit);

		if($this->wire->input->post('submit_save')) {
			$form->processInput($this->wire->input->post);
			$content = $form->get('llms_content')->value;

			// Save to module options
			$this->wire->modules->saveConfig($this->className, array(
				'llms_content' => $content
			));

			$this->wire->session->redirect('./');
		}

		return $form->render();
	}
}
