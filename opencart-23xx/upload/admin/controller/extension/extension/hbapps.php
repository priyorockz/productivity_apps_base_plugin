<?php
class ControllerExtensionExtensionHbapps extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/extension/hbapps');

		$this->load->model('extension/extension');

		$this->getList();
	}

	public function install() {
		$this->load->language('extension/extension/hbapps');

		$this->load->model('extension/extension');

		if ($this->validate()) {
			$this->model_extension_extension->install('hbapps', $this->request->get['extension']);

			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/hbapps/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/hbapps/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('extension/hbapps/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function uninstall() {
		$this->load->language('extension/extension/hbapps');

		$this->load->model('extension/extension');

		if ($this->validate()) {
			$this->model_extension_extension->uninstall('hbapps', $this->request->get['extension']);

			// Call uninstall method if it exsits
			$this->load->controller('extension/hbapps/' . $this->request->get['extension'] . '/uninstall');

			$this->session->data['success'] = $this->language->get('text_success');
		}
		
		$this->getList();
	}

	protected function getList() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_no_results'] = $this->language->get('text_no_results');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');

		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_install'] = $this->language->get('button_install');
		$data['button_uninstall'] = $this->language->get('button_uninstall');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$extensions = $this->model_extension_extension->getInstalled('hbapps');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_APPLICATION . 'controller/extension/hbapps/' . $value . '.php') && !is_file(DIR_APPLICATION . 'controller/hbapps/' . $value . '.php')) {
				$this->model_extension_extension->uninstall('hbapps', $value);

				unset($extensions[$key]);
			}
		}

		$this->load->model('setting/store');
		$this->load->model('setting/setting');

		$stores = $this->model_setting_store->getStores();

		$data['extensions'] = array();
		
		// Compatibility code for old extension folders
		$files = glob(DIR_APPLICATION . 'controller/{extension/hbapps,hbapps}/*.php', GLOB_BRACE);

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				
				$this->load->language('extension/hbapps/' . $extension);
					
				$store_data = array();
				
				$store_data[] = array(
					'name'   => $this->config->get('config_name'),
					'edit'   => $this->url->link('extension/hbapps/' . $extension, 'token=' . $this->session->data['token'] . '&store_id=0', true)
				);
									
				foreach ($stores as $store) {
					$store_data[] = array(
						'name'   => $store['name'],
						'edit'   => $this->url->link('extension/hbapps/' . $extension, 'token=' . $this->session->data['token'] . '&store_id=' . $store['store_id'], true)
					);
				}
				
				$data['extensions'][] = array(
					'name'      => $this->language->get('heading_title'),
					'install'   => $this->url->link('extension/extension/hbapps/install', 'token=' . $this->session->data['token'] . '&extension=' . $extension, true),
					'uninstall' => $this->url->link('extension/extension/hbapps/uninstall', 'token=' . $this->session->data['token'] . '&extension=' . $extension, true),
					'installed' => in_array($extension, $extensions),
					'store'     => $store_data
				);
			}
		}

		$this->response->setOutput($this->load->view('extension/extension/hbapps', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/extension/hbapps')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}