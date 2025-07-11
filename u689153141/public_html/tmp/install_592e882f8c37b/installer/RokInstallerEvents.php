<?php
/**
 * @version   4.1.11 July 2, 2013
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2013 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokInstallerEvents extends JPlugin
{

	const STATUS_ERROR     = 'error';
	const STATUS_INSTALLED = 'installed';
	const STATUS_UPDATED   = 'updated';

	protected static $messages = array();

	/**
	 * @var JInstaller
	 */
	protected $toplevel_installer;

	public function setTopInstaller(&$installer)
	{
		$this->toplevel_installer = $installer;
	}

	public function __construct(&$subject, $config = array())
	{

		parent::__construct($subject, $config);

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$install_html_file = dirname(__FILE__) . '/../install.html';
		$install_css_file  = dirname(__FILE__) . '/../install.css';
		$tmp_path          = JPATH_ROOT . '/tmp';
		if (JFolder::exists($tmp_path)) {
			// Copy install.css to tmp dir for inclusion
			JFile::copy($install_css_file, $tmp_path . '/install.css');
			JFile::copy($install_html_file, $tmp_path . '/install.html');
		}

	}

	public static function addMessage($package, $status, $message = '')
	{
		self::$messages[] = call_user_func_array(array('RokInstallerEvents', $status), array($package, $message));
	}



	/**
	 * @return string
	 */
	protected static function loadCss()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$buffer            = '';
		// Drop out Style
		if (file_exists( JPATH_ROOT . '/tmp/install.html')) {
			$buffer .= JFile::read(JPATH_ROOT . '/tmp/install.html');
		}
		return $buffer;
	}


	/**
	 * @param $package
	 * @param $msg
	 *
	 * @return string
	 */
	public static function error($package, $msg)
	{
		ob_start();
		?>
    <li class="rokinstall-failure"><span class="rokinstall-row"><span
            class="rokinstall-icon"><span></span></span><?php echo $package['name'];?> installation failed</span>
            <span class="rokinstall-errormsg">
                <?php echo $msg; ?>
            </span>
    </li>
	<?php
		$out = ob_get_clean();
		return $out;
	}

	/**
	 * @param $package
	 *
	 * @return string
	 */
	public static function installed($package)
	{
		ob_start();
		?>
    <li class="rokinstall-success"><span class="rokinstall-row"><span
            class="rokinstall-icon"><span></span></span><?php echo $package['name'];?>
        installation was successful</span></li>
	<?php
		$out = ob_get_clean();
		return $out;
	}

	/**
	 * @param $package
	 *
	 * @return string
	 */
	public static function updated($package)
	{
		ob_start();
		?>
    <li class="rokinstall-update"><span class="rokinstall-row"><span
            class="rokinstall-icon"><span></span></span><?php echo $package['name'];?> update was successful</span>
    </li>
	<?php
		$out = ob_get_clean();
		return $out;
	}

	public function onExtensionAfterInstall($installer, $eid)
	{
		$lang = JFactory::getLanguage();
		$lang->load('install_override', dirname(__FILE__), $lang->getTag(), true);
		$this->toplevel_installer->set('extension_message', $this->getMessages());
	}

	public function onExtensionAfterUpdate($installer, $eid)
	{
		$lang = JFactory::getLanguage();
		$lang->load('install_override', dirname(__FILE__), $lang->getTag(), true);
		$this->toplevel_installer->set('extension_message', $this->getMessages());
	}


	protected function getMessages()
	{
		$buffer = '';
		$buffer .= self::loadCss();
		$buffer .= '<div id="rokinstall-logo"><ul id="rokinstall-status">';
		$buffer .= implode('', self::$messages);
		$buffer .= '</ul></div>';
		return $buffer;
	}


}
