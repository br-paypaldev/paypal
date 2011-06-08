<?php
/**
 * Observador de Notificações de Pagamento Instantâneo
 */
class InstantPaymentNotification {
	/**
	 * @var	string
	 */
	private $endpoint = 'https://www.paypal.com';

	/**
	 * @var	IPNHandler
	 */
	private $ipnHandler;

	/**
	 * Constroi o objeto que receberá as notificações de pagamento
	 * instantâneas do PayPal..
	 * @param	boolean $sandbox Define se será utilizado o Sandbox
	 * @throws	InvalidArgumentException
	 */
	public function __construct( $sandbox = false ) {
		if ( !!$sandbox ) {
			$this->endpoint = 'https://www.sandbox.paypal.com';
		}

		$this->endpoint .= '/cgi-bin/webscr?cmd=_notify-validate';
	}

	/**
	 * Aguarda por notificações de pagamento instantânea; Caso uma nova
	 * notificação seja recebida, faz a verificação e notifica um manipulador
	 * com o status (verificada ou não) e a mensagem recebida.
	 * @see		InstantPaymentNotification::setIPNHandler()
	 * @throws	BadMethodCallException Caso o método seja chamado antes
	 * de um manipulador ter sido definido ou nenhum email de recebedor
	 * tenha sido informado.
	 */
	public function listen() {
		if ( $this->ipnHandler !== null && count( $this->receiverEmail ) >= 1 ) {
			if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
				if ( filter_input( INPUT_POST , 'receiver_email' , FILTER_VALIDATE_EMAIL ) ) {
					$curl = curl_init();

					curl_setopt( $curl , CURLOPT_URL , $this->endpoint );
					curl_setopt( $curl , CURLOPT_SSL_VERIFYPEER , false );
					curl_setopt( $curl , CURLOPT_RETURNTRANSFER , 1 );
					curl_setopt( $curl , CURLOPT_POST , 1 );
					curl_setopt( $curl , CURLOPT_POSTFIELDS , http_build_query( $_POST ) );

					$response = curl_exec( $curl );
					$error = curl_error( $curl );
					$errno = curl_errno( $curl );

					curl_close( $curl );

					if ( empty( $error ) && $errno == 0 ) {
						$this->ipnHandler->handle( $response == 'VERIFIED' , $_POST );
					}
				}
			}
		} else {
			throw new BadMethodCallException( 'Nenhum manipulador de mensagem ou email foi definido' );
		}
	}

	/**
	 * Define o objeto que irá manipular as notificações de pagamento
	 * instantâneas enviadas pelo PayPal.
	 * @param	IPNHandler $ipnHandler
	 */
	public function setIPNHandler( IPNHandler $ipnHandler ) {
		$this->ipnHandler = $ipnHandler;
	}
}