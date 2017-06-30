<?php

class Ebizmarts_BakerlooPayment_Block_Form_Iframe extends Mage_Core_Block_Template {

	protected function _toHtml() {

		$html  = '<html><body>';

		try {

			$form = new Varien_Data_Form;
			$form
			->setAction($this->getPostUrl())
			->setId($this->getCode())
			->setName($this->getCode())
			->setMethod('POST')
			->setUseContainer(true);

			foreach($this->getPostFields() as $name => $value) {
				$form->addField(
					$name, 'hidden',
					array (
						'name'  => $name,
						'value' => $value
					)
				);
			}

			$html .= '<code>' . $this->__('Redirecting to bank...') . '</code>';
			$html .= $form->toHtml();
			$html .= '<script type="text/javascript">document.getElementById("' . $this->getHtmlFormId() . '").submit();</script>';

		} catch (Exception $e) {
			Mage::logException($e);

			$html .= "<h1>{$e->getMessage()}</h1>";
		}

		$html .= '</body></html>';

		return $html;

	}

	public function getHtmlFormId() {
		return $this->getCode();
	}

    protected function _toErrorHtml($errorMessage) {

        $html  = '<html><body>';
        $html .= '<code>' . $this->__('[Error] ') . $this->__($errorMessage) . '</code>';
        $html .= '</body></html>';

        return $html;

    }

}