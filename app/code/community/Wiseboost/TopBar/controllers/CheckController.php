<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Wiseboost
 * @package    Wiseboost_TopBar
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Wiseboost_TopBar_CheckController extends Mage_Adminhtml_Controller_Action 
{     

	public function preDispatch()
	{
		$answer = array();
		$answer['error']['code'] = -1;
		$answer['error']['msg'] = "";	// admin not logged in.

		try 
		{
			ob_start();
			
			$admin_logged_in = false;
			
			// Ensure we're in the admin session namespace for checking the admin user...

			// version of Magento 1.7 use 'adminhtml', 1.5 use 'PHPSESSID'. This might be configurable, 
			// in this case the bar will never show up.
			$sessionName = ((array_key_exists(('adminhtml'), $_COOKIE)) ? 'adminhtml' : 'PHPSESSID');
			
			Mage::getSingleton('core/session', array('name' => $sessionName))->start();

			$admin_logged_in = Mage::getSingleton('admin/session', array('name' => $sessionName))->isLoggedIn();
								

			// ..get back to the original.
			Mage::getSingleton('core/session', array('name' => $this->_sessionNamespace))->start();

			if ($admin_logged_in)
			{
				$answer['error']['code'] = 1;
				$answer['error']['msg'] = "";	// admin logged in.

				
				// retrieve POST parameters
				$storeId = (int)Mage::app()->getFrontController()->getRequest()->getParam('store_id', false);
				$cleanBaseURL = Mage::app()->getFrontController()->getRequest()->getParam('cleanbaseurl', false);
				
				
				// $vPath = 'orange.html';
				$vPath = Mage::app()->getFrontController()->getRequest()->getParam('path', false);
				$oRewrite = Mage::getModel('core/url_rewrite')
								->setStoreId(Mage::app()->getStore($storeId)->getId())
								->loadByRequestPath($vPath);
								
								
				$iProductId = $oRewrite->getProductId();
				
				$answer['admin']['url'] = "";
				if ($iProductId != null)
				{
					$oProduct = Mage::getModel('catalog/product')->load($iProductId);
					$answer['admin']['url'] = Mage::helper("adminhtml")->getUrl("adminhtml/catalog_product/edit/",array("id"=>$iProductId));
				}
				
				
				// retrieve store information
				$answer['store']['id'] = $storeId;
				$answer['store']['name'] = Mage::app()->getStore($storeId)->getName();
				$answer['store']['cleanbaseurl'] = $cleanBaseURL;
				
				
				// retrieve user information
				$user = Mage::getSingleton('admin/session');
				$answer['user']['name'] = $user->getUser()->getUsername();
				$answer['user']['url'] = Mage::helper("adminhtml")->getUrl("adminhtml/", array());
				
				
				$html = $this->getTopBarHTML($answer);
				
				$output = ob_get_contents();
				ob_end_clean();
				if ($output == "") 
				{
					// return the HTML code
					echo $html;
					return ;
				}
			} 
			else
			{
				ob_end_clean();
			}
		} 
		catch (Exception $e) 
		{
			// echo 'Caught exception: ',  $e->getMessage(), "\n";
			ob_end_clean();
		}

		echo "";
	}

    public function indexAction() 
	{
    }
	
	private function getTopBarHTML($answer)
	{
		$html = '';
		
		$html .= '<div class="wiseboost_topbar_header" id="wiseboost_topbar" style="display: block;">';
		$html .= '	<div class="wiseboost_topbar_header_inner">';
		$html .= '		<ul class="wiseboost_topbar_horz_list wiseboost_topbar_horz_list_left">';
		$html .= '			<li id="wiseboost_topbar_logo">';
		$html .= '				<span><a href="http://www.wiseboost.com/" target="_blank" style="position">';
		$html .= '					<img src="' . $answer['store']['cleanbaseurl'] . '/skin/frontend/base/default/images/Wiseboost_TopBar/logo.png" />';
		$html .= '				</a></span>';
		$html .= '			</li>';
		$html .= '			<li>';
		$html .= '				<span id="wiseboost_topbar_storename">' . $answer['store']['name'] . '</span>';
		$html .= '			</li>';
		if ($answer['admin']['url'] != "") 
		{
			$html .= '			<li>';
			$html .= '				<a href="'. $answer['admin']['url'] . '"" id="wiseboost_topbar_edit" target="_blank">Edit Product (will open in a new window)</a>';
			$html .= '			</li>';
		} 
		$html .= '		</ul>';
		$html .= '		<ul class="wiseboost_topbar_horz_list wiseboost_topbar_horz_list_right">';
		if ($answer['user']['name'] != "") 
		{
			$html .= '			<li id="wiseboost_topbar_admin">';
			$html .= '				<span>Logged in as : <a id="wiseboost_topbar_username" href="' . $answer['user']['url'] . '">' . $answer['user']['name'] . '</a></span>';
			$html .= '			</li>'; 
		}
		$html .= '			<ul class="nav">'; 
		$html .= '				<li><a class="header_top_link" title="" href="/account">TAG Conférence</a>'; 
		$html .= '					<ul>'; 
		$html .= '						<li><a class="header_top_link" title="" href="/account/logout">Déconnexion</a></li>'; 
		$html .= '					</ul>'; 
		$html .= '				</li>'; 
		$html .= '			</ul>'; 
		$html .= '		</ul>'; 
		$html .= '	</div>';
		$html .= '</div>';
		
		return $this->sanitize_output($html);
	}
	
	private function sanitize_output($buffer)
	{
		$search = array(
			'/\>[^\S ]+/s', //strip whitespaces after tags, except space
			'/[^\S ]+\</s', //strip whitespaces before tags, except space
			'/(\s)+/s'  // shorten multiple whitespace sequences
			);
		$replace = array(
			'>',
			'<',
			'\\1'
			);
		$buffer = preg_replace($search, $replace, $buffer);

		return $buffer;
	}
}
?>