<?php
/**
 * 邮件发送类
 * 仅支持发送纯文本和HTML内容邮件
 * 需要的php扩展，sockets
 * @example
 * $mail = new MySendMail();
 * $mail->setServer("XXXXX", "XXXXX@XXXXX", "XXXXX"); 设置smtp服务器
 * $mail->setFrom("XXXXX"); 设置发件人
 * $mail->setReceiver("XXXXX"); 设置收件人
 * $mail->setMailInfo("test", "<b>test</b>"); 设置邮件主题、内容
 * $mail->sendMail(); 发送
 */
class MySendMail {
	/**
	 * @var string 邮件传输代理用户名
	 * @access private
	 */
	private $_userName;
	/**
	 * @var string 邮件传输代理密码
	 * @access private
	 */
	private $_password;
	/**
	 * @var string 邮件传输代理服务器地址
	 * @access private
	 */
	private $_sendServer;
	/**
	 * @var int 邮件传输代理服务器端口
	 * @access protected
	 */
	protected $_port = 25;
	/**
	 * @var string 发件人
	 * @access protected
	 */
	protected $_from;
	/**
	 * @var string 收件人
	 * @access protected
	 */
	protected $_to;
	/**
	 * @var string 主题
	 * @access protected
	 */
	protected $_subject;
	/**
	 * @var string 邮件正文
	 * @access protected
	 */
	protected $_body;
	/**
	 * @var reource socket资源
	 * @access protected
	 */
	protected $_socket;
	/**
	 * @var string 错误信息
	 * @access protected
	 */
	protected $_errorMessage;
	/**
	 * 设置邮件传输代理，如果是可以匿名发送有邮件的服务器，只需传递代理服务器地址就行
	 * @access public
	 * @param string $server 代理服务器的ip或者域名
	 * @param string $username 认证账号
	 * @param string $password 认证密码
	 * @param int $port 代理服务器的端口，smtp默认25号端口
	 * @return boolean
	 */
	public function setServer($server, $username = "", $password = "", $port = 25) {
		$this -> _sendServer = $server;
		$this -> _port = $port;
		if (!empty($username)) {
			$this -> _userName = base64_encode($username);
		}
		if (!empty($password)) {
			$this -> _password = base64_encode($password);
		}
		return true;
	}

	/**
	 * 设置发件人
	 * @access public
	 * @param string $from 发件人地址
	 * @return boolean
	 */
	public function setFrom($from) {
		$this -> _from = $from;
		return true;
	}

	/**
	 * 设置收件人
	 * @access public
	 * @param string $to 收件人地址
	 * @return boolean
	 */
	public function setReceiver($to) {
		$this -> _to = $to;
		return true;
	}

	/**
	 * 设置邮件信息
	 * @access public
	 * @param string $body 邮件主题
	 * @param string $subject 邮件主体内容，可以是纯文本，也可是是HTML文本
	 * @return boolean
	 */
	public function setMailInfo($subject, $body) {
		$this -> _subject = $subject;
		$this -> _body = base64_encode($body);
		if (!empty($attachment)) {
			$this -> _attachment = $attachment;
		}
		return true;
	}

	/**
	 * 发送邮件
	 * @access public
	 * @return boolean
	 */
	public function sendMail() {
		$command = $this -> getCommand();
		$this -> socket();
		foreach ($command as $value) {
			if ($this -> sendCommand($value[0], $value[1])) {
				continue;
			} else {
				return false;
			}
		}
		//其实这里也没必要关闭，smtp命令：QUIT发出之后，服务器就关闭了连接，本地的socket资源会自动释放
		$this -> close();
		echo 'Mail OK!';
		return true;
	}

	/**
	 * 返回错误信息
	 * @return string
	 */
	public function error() {
		if (!isset($this -> _errorMessage)) {
			$this -> _errorMessage = "";
		}
		return $this -> _errorMessage;
	}

	/**
	 * 返回mail命令
	 * @access protected
	 * @return array
	 */
	protected function getCommand() {
		$separator = "----=_Part_" . md5($this -> _from . time()) . uniqid();
		//分隔符
		$command = array( array(
				"HELO sendmail\r\n",
				250
			));
		if (!empty($this -> _userName)) {
			$command[] = array(
				"AUTH LOGIN\r\n",
				334
			);
			$command[] = array(
				$this -> _userName . "\r\n",
				334
			);
			$command[] = array(
				$this -> _password . "\r\n",
				235
			);
		}
		//设置发件人
		$command[] = array(
			"MAIL FROM: <" . $this -> _from . ">\r\n",
			250
		);
		$header = "FROM: <" . $this -> _from . ">\r\n";
		//设置收件人
		$command[] = array(
			"RCPT TO: <" . $this -> _to . ">\r\n",
			250
		);
		$header .= "TO: <" . $this -> _to . ">\r\n";
		$header .= "Subject: " . $this -> _subject . "\r\n";
		$header .= "Content-Type: multipart/alternative;\r\n";
		//邮件头分隔符
		$header .= "\t" . 'boundary="' . $separator . '"';
		$header .= "\r\nMIME-Version: 1.0\r\n";
		$header .= "\r\n--" . $separator . "\r\n";
		$header .= "Content-Type:text/html; charset=utf-8\r\n";
		$header .= "Content-Transfer-Encoding: base64\r\n\r\n";
		$header .= $this -> _body . "\r\n";
		$header .= "--" . $separator . "\r\n";
		//结束数据
		$header .= "\r\n.\r\n";
		$command[] = array(
			"DATA\r\n",
			354
		);
		$command[] = array(
			$header,
			250
		);
		$command[] = array(
			"QUIT\r\n",
			221
		);
		return $command;
	}

	/**
	 * 发送命令
	 * @access protected
	 * @param string $command 发送到服务器的smtp命令
	 * @param int $code 期望服务器返回的响应吗
	 * @return boolean
	 */
	protected function sendCommand($command, $code) {
		echo 'Send command:' . $command . ',expected code:' . $code . '<br />';
		//发送命令给服务器
		try {
			if (socket_write($this -> _socket, $command, strlen($command))) {
				//当邮件内容分多次发送时，没有$code，服务器没有返回
				if (empty($code)) {
					return true;
				}
				//读取服务器返回
				$data = trim(socket_read($this -> _socket, 1024));
				echo 'response:' . $data . '<br /><br />';
				if ($data) {
					$pattern = "/^" . $code . "/";
					if (preg_match($pattern, $data)) {
						return true;
					} else {
						$this -> _errorMessage = "Error:" . $data . "|**| command:";
						return false;
					}
				} else {
					$this -> _errorMessage = "Error:" . socket_strerror(socket_last_error());
					return false;
				}
			} else {
				$this -> _errorMessage = "Error:" . socket_strerror(socket_last_error());
				return false;
			}
		} catch(Exception $e) {
			$this -> _errorMessage = "Error:" . $e -> getMessage();
		}
	}

	/**
	 * 建立到服务器的网络连接
	 * @access private
	 * @return boolean
	 */
	private function socket() {
		if (!function_exists("socket_create")) {
			$this -> _errorMessage = "Extension sockets must be enabled";
			return false;
		}
		//创建socket资源
		$this -> _socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
		if (!$this -> _socket) {
			$this -> _errorMessage = socket_strerror(socket_last_error());
			return false;
		}
		socket_set_block($this -> _socket);
		//设置阻塞模式
		//连接服务器
		if (!socket_connect($this -> _socket, $this -> _sendServer, $this -> _port)) {
			$this -> _errorMessage = socket_strerror(socket_last_error());
			return false;
		}
		socket_read($this -> _socket, 1024);
		return true;
	}

	/**
	 * 关闭socket
	 * @access private
	 * @return boolean
	 */
	private function close() {
		if (isset($this -> _socket) && is_object($this -> _socket)) {
			$this -> _socket -> close();
			return true;
		}
		$this -> _errorMessage = "No resource can to be close";
		return false;
	}

}

/**************************** Test ***********************************/
$mail = new MySendMail();
$mail -> setServer("smtp.qq.com", "ferock@qq.com", "fer1234567");
$mail -> setFrom("ferock@qq.com");
$mail -> setReceiver("ferock@gmail.com");
$mail -> setMailInfo("test", "<b>test</b>");
$mail -> sendMail();
