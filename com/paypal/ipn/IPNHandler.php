<?php
/**
 * Interface para definição de um manipulador de notificação
 * de pagamento instantânea.
 */
interface IPNHandler {
	/**
	 * Manipula uma notificação de pagamento instantânea recebida
	 * pelo PayPal.
	 * @param	boolean $isVerified Identifica que a mensagem foi
	 * verificada como tendo sido enviada pelo PayPal.
	 * @param	array $message Mensagem completa enviada pelo
	 * PayPal.
	 */
	public function handle( $isVerified , array $message );
}