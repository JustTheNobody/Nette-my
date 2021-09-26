<?php

/**
 * Soubor se sluzbou pro odesilani emailu
 *
 * @package  CMS
 * @subpackage  Model
 * @author   Jan Vaclavik <jan.vaclavik@impnet.cz>
 * @version  $Revision: 1.0 $
 */

namespace App\Model;

use Nette,
    App\Translator,
    Tracy\ILogger,
    Tracy\Debugger;

/**
 * Zakladni trida pro sluzbu Mailer - metody pro odesilani emailu
 *
 * @package  CMS
 * @author   Jan Vaclavik <jan.vaclavik@impnet.cz>
 * @version  $Revision: 1.0 $
 */
final class Mailer
{

  use \Nette\SmartObject;

  /** @var Nette\Http\Request Sluzba requestu */
  private $httpRequest;

  /** @var Nette\Mail\IMailer Sluzba maileru */
  private $mailer;

  /** @var Translator\FrontendTranslator Sluzba prekladace (frontendoveho) */
  private $translator;

  /** @var Settings Model nastaveni */
  private $settings;

  /** @var Languages Model nastaveni */
  private $languages;

  /** @var Nette\Application\LinkGenerator Sluzba pro generovani linku */
  private $linkGenerator;

  /** @var string Lokalizace, na kterou je sluzba nastvena ('cs', ...) */
  private $locale;

  /** @var bool Pokud se nastavi na true, zamezi se zasilani (kvuli testovani) */
  private $sendingDisabled = false;

    /**
     * Konstruktor
     * @param Nette\Http\Request $httpRequest Sluzba request
     * @param Nette\Mail\IMailer $imailer Sluzba imailer
     * @param Translator\FrontendTranslator $translator Sluzba translator (front)
     * @param Settings $settings Model
     * @param Languages $languages Model
     * @param Nette\Application\LinkGenerator $lGenerator Generator linku
     * @return self
     */
  public function __construct( 
      Nette\Http\Request $httpRequest,
      Nette\Mail\IMailer $imailer,
      Translator\FrontendTranslator $translator,
      Settings $settings, Languages $languages,
      Nette\Application\LinkGenerator $lGenerator 
    ) {
        $this->httpRequest   = $httpRequest;
        $this->mailer        = $imailer;
        $this->translator    = $translator;
        $this->settings      = $settings;
        $this->languages     = $languages;
        $this->linkGenerator = $lGenerator;
    }

  /**
   * Prida do hlavicky link pro zobrazeni online verze emailu
   * @param string $body Telo bez linku...
   * @return string Telo i s hlavickou s linkem
   */
  private function addWebpageLink( $body )
  {
    // Check na pocet logovanych emailu - pokud je >= 10000, pak bude nutne nejstarsi odmazat
    if ( $this->settings->getDatabase()->table( 'base_sentEmails' )->count() >= 5000 )
    {
      // Smazeme poslednich 100 zaznamu
      $this->settings->getDatabase()->table( 'base_sentEmails' )->order( 'created DESC' )->limit( 100 )->delete();
    }

    $hash      = \Tools\Utils::generateHash( 30 );
    $emailData = [ 'body' => $body, 'created' => date( 'Y-m-d H:i:s' ), 'hash' => $hash ];
    $row       = $this->settings->getDatabase()->table( 'base_sentEmails' )->insert( $emailData );

    $webpageLink = $this->linkGenerator->link( "Core:EmailOnline:", [
        'id'    => $row->id,
        'token' => $hash ] );

    $preBody = '<p style="font-size: 10px; width: 100%; text-align: center;">' . $this->translator->translate( 'show_as_webpage' ) . ' <b><a href="' . $webpageLink . '">' . $this->translator->translate( 'show_online' ) . '</a></b></p>';
    $body    = $preBody . $body;

    return $body;
  }

  /**
   * Odesle e-mail vytvoreny podle adminem-editovatelne sablony zpravy
   * @param int $idMessage ID zpravy
   * @param string|string[] $to Komu (pokud je zadano pole, odesle se vsem)
   * 							  Pokud retezec obsahuje carky, bude bran jako
   * 							  nekolik e-mailovych adres oddelenych carkou
   * @param string[] $paramsToAdd Parametry
   *                 POZN: index 'placeholders' v tomto poli parametru je vyhrazen
   *                 pro pripadne pole transformacnich retezcu, napr. [ '{{number}}' => 123456 ]
   *                 Tyhle retezce budou pozdeji v sablone dynamicky nahrazeny
   * @param string $subjectPrefix Pripadny prefix pro predmet zpravy
   * @param string|string[]|NULL $attachment Cesta/cesty k priloze
   * @param string|NULL Pokud je zadano, explicitne vynuti slovnikovou lokalizaci
   * @return void
   */
  public function sendCustomizedEmailMessage( $idMessage, $to,
                                              $paramsToAdd = [],
                                              $subjectPrefix = '',
                                              $attachment = NULL, $lang = NULL )
  {
    if ( isset( $lang ) )
    {
      $this->translator->setLang( $lang );
      $this->setLang( $lang );
    }

    $settings = $this->settings->find( $this->locale );
    $message  = $this->languages->findMessage( $idMessage, $this->locale );

    if ( !$message )
    {
      Debugger::log( "Mailer::sendCustomizedEmailMessage(): Nepodarilo se nalezt zpravu s ID: " . $idMessage, \Tracy\ILogger::ERROR );
      return;
    }

    if ( !empty( $paramsToAdd['placeholders'] ) )
    {
      $obj     = new \App\Filters\interpret();
      $subject = $obj( $message->locale()->subject, $paramsToAdd['placeholders'] );
    }
    else
    {
      $subject = $message->locale()->subject;
    }

    // Naplneni parametru predanych sablone
    $params              = [ 'placeholders' => [ '{{webUrl}}'   => $this->httpRequest->getUrl()->getHostUrl() . $this->httpRequest->getUrl()->getBasePath(),
            '{{webName}}'  => $settings->locale()->webName, '{{webPhone}}' => $settings->locale()->phone,
            '{{webEmail}}' => $settings->locale()->email ] ];
    $params['useHeader'] = $message->data()->useHeader;
    $params['useFooter'] = $message->data()->useFooter;
    $params['message']   = $message->locale()->content;
    $params['settings']  = $settings;
    $params['url']       = $this->httpRequest->getUrl();
    // Pridame pripadne uzivatelske parametry
    $params              = array_merge_recursive( $params, $paramsToAdd );

    $this->sendEmailTemplate( $to, $subjectPrefix . $subject, 'message.latte', $params, $settings->locale()->email, $attachment, $lang );
  }

  /**
   * Odesle e-mail (textovy)
   * @param string|string[] $to Komu (pokud je zadano pole, odesle se vsem)
   * 							  Pokud retezec obsahuje carky, bude bran jako
   * 							  nekolik e-mailovych adres oddelenych carkou
   * @param string|NULL $subject Predmet
   * @param string|NULL $body Vlastni telo e-mailu
   * @param string|NULL $from Od
   * @param string|NULL $attachment Cesta k priloze
   * @return void
   */
  public function sendEmail( $to, $subject = NULL, $body = NULL, $from = NULL,
                             $attachment = NULL, $reply = NULL )
  {
    $mail     = new \Nette\Mail\Message;
    $settings = $this->settings->find( $this->locale );

    if ( is_array( $to ) )
    {
      foreach ( $to as $t )
        $mail->addTo( $t );
    }
    else
    {
      // Odstranime pripadne whitespaces
      $tmpEmail = preg_replace( '/\s+/', '', $to );
      // Rozdelime adresy dle pripadnych carek
      $emails   = explode( ',', $tmpEmail );
      foreach ( $emails as $e )
        $mail->addTo( $e );
    }

    // Pokud neni zadano From, pouzije se e-mail z modelu (DB)
    if ( !isset( $from ) )
      $from = $settings->locale()->email;

    // Nastavíme adresy pro odpověď
    if( !empty( $reply ) )
    {
      $replies = preg_replace( '/\s+/', '', $reply );
      $replies = explode( ',', $replies );
      
      foreach( $replies as $replyTo )
        $mail->addReplyTo( $replyTo );
    }

    // Odstranime pripadne whitespaces
    $tmpEmail = preg_replace( '/\s+/', '', $from );
    // Rozdelime adresy dle pripadnych carek
    $emails   = explode( ',', $tmpEmail );
    // Nastavime "Od" jako prvni nalezeny e-mail z pripadnych vice oddelenych carkou
    $mail->setFrom( $emails[0] );

    if ( isset( $subject ) )
      $mail->setSubject( $subject );

    if ( isset( $body ) )
    {
      // Zapustime hlavicku s online odkazem
      $body = $this->addWebpageLink( $body );
      $mail->setHtmlBody( $body );
    }

    if ( isset( $attachment ) )
    {
      if ( is_array( $attachment ) )
      {
        foreach ( $attachment as $a )
        {
          if ( !empty( $a ) )
            $mail->addAttachment( $a );
        }
      }
      else
      {
        $mail->addAttachment( $attachment );
      }
    }

    try
    {
      if ( !$this->sendingDisabled )
        $this->mailer->send( $mail );
    }
    catch ( \Exception $e )
    {
      Debugger::log( "Mailer::sendEmail(): " . $e->getMessage(), ILogger::ERROR );
    }
  }

  /**
   * Odesle e-mail predany parametrem
   * @param \Nette\Mail\Message $message Zprava k odeslani
   * @return void
   */
  public function sendEmailMessage( \Nette\Mail\Message $message )
  {
    try
    {
      if ( !$this->sendingDisabled )
        $this->mailer->send( $message );
    }
    catch ( \Exception $e )
    {
      Debugger::log( "Mailer::sendEmailMessage(): " . $e->getMessage(), ILogger::ERROR );
    }
  }

  /**
   * Odesle e-mail vytvoreny podle latte sabolny
   * @param string|string[] $to Komu (pokud je zadano pole, odesle se vsem)
   * 							  Pokud retezec obsahuje carky, bude bran jako
   * 							  nekolik e-mailovych adres oddelenych carkou
   * @param string|NULL $subject Predmet
   * @param string|NULL $template Soubor se sablonou (v FrontendModule/templates/Email)
   * @param mixed[] $paramsToAdd Pole parametru predavane sablone
   * @param string|NULL $from Od
   * @param string|string[]|NULL $attachment Cesta/cesty k priloze
   * @param string|NULL Pokud je zadano, explicitne vynuti slovnikovou lokalizaci
   * @return void
   */
  public function sendEmailTemplate( $to, $subject = NULL, $template = NULL,
                                     $paramsToAdd = [], $from = NULL,
                                     $attachment = NULL, $lang = NULL,
                                     $reply = NULL )
  {
    $path  = '/../FrontendModule/templates/Email/';
    $mail  = new \Nette\Mail\Message;
    $latte = new \Latte\Engine;

    if ( isset( $lang ) )
    {
      $this->translator->setLang( $lang );
      $this->setLang( $lang );
    }

    // Explicitne nastavime sablone Frontendovy prekladac
    $latte->addFilter( 'translate', [ $this->translator, 'translate' ] );
    // Explicitne nastavime interpreter placeholderu
    $latte->addFilter( 'interpret', new \App\Filters\interpret );

    $settings = $this->settings->find( $this->locale );

    if ( is_array( $to ) )
    {
      foreach ( $to as $t )
        $mail->addTo( $t );
    }
    else
    {
      // Odstranime pripadne whitespaces
      $tmpEmail = preg_replace( '/\s+/', '', $to );
      // Rozdelime adresy dle pripadnych carek
      $emails   = explode( ',', $tmpEmail );
      foreach ( $emails as $e )
        $mail->addTo( $e );
    }

    // Pokud neni zadano From, pouzije se e-mail z modelu (DB)
    if ( !isset( $from ) )
      $from = $settings->locale()->email;

    // Nastavíme adresy pro odpověď
    if( !empty( $reply ) )
    {
      $replies = preg_replace( '/\s+/', '', $reply );
      $replies = explode( ',', $replies );
      
      foreach( $replies as $replyTo )
        $mail->addReplyTo( $replyTo );
    }

    // Odstranime pripadne whitespaces
    $tmpEmail = preg_replace( '/\s+/', '', $from );
    // Rozdelime adresy dle pripadnych carek
    $emails   = explode( ',', $tmpEmail );
    // Nastavime "Od" jako prvni nalezeny e-mail z pripadnych vice oddelenych carkou
    $mail->setFrom( $emails[0] );

    if ( isset( $subject ) )
      $mail->setSubject( $subject );

    $defaultParams = [ 'settings'     => $settings,
        'placeholders' => [ '{{webUrl}}'   => $this->httpRequest->getUrl()->getHostUrl() . $this->httpRequest->getUrl()->getBasePath(),
            '{{webName}}'  => $settings->locale()->webName, '{{webPhone}}' => $settings->locale()->phone,
            '{{webEmail}}' => $settings->locale()->email ] ];

    // Pridame pripadne uzivatelske parametry
    $params = array_merge( $defaultParams, $paramsToAdd );

    // Kvuli spatnemu mergovani podpoli pridano jeste mergovani kazdeho podpole zvlast
    foreach ( $params as $key => $value )
    {
      if ( isset( $defaultParams[$key] ) && isset( $paramsToAdd[$key] ) )
      {
        if ( is_array( $defaultParams[$key] ) && is_array( $paramsToAdd[$key] ) )
        {
          $params[$key] = array_merge( $defaultParams[$key], $paramsToAdd[$key] );
        }
      }
    }


    if ( isset( $template ) )
    {
      $body = $latte->renderToString( __DIR__ . $path . $template, $params );
    }
    else
    {
      if ( !isset( $params['nadpis'] ) )
        $params['nadpis'] = '';
      if ( !isset( $params['telo'] ) )
        $params['telo']   = '';
      if ( !isset( $params['podpis'] ) )
        $params['podpis'] = '';

      $body = $latte->renderToString( __DIR__ . $path . 'default.latte', $params );
    }

    // Zapustime hlavicku s online odkazem
    $body = $this->addWebpageLink( $body );
    $mail->setHtmlBody( $body );

    if ( isset( $attachment ) )
    {
      if ( is_array( $attachment ) )
      {
        foreach ( $attachment as $a )
        {
          if ( !empty( $a ) )
            $mail->addAttachment( $a );
        }
      }
      else
      {
        $mail->addAttachment( $attachment );
      }
    }

    try
    {
      if ( !$this->sendingDisabled )
        $this->mailer->send( $mail );
    }
    catch ( \Exception $e )
    {
      Debugger::log( "Mailer::sendEmailTemplate(): " . $e->getMessage(), ILogger::ERROR );
    }
  }

  /**
   * Odesle chybovy e-mail
   * @param string $message Hlavni zprava
   * @param string $to Komu se odesila
   * @return void
   */
  public function sendErrorEmail( $message, $to )
  {
    $mail     = new Nette\Mail\Message;
    $settings = $this->settings->get();
    $mail->addTo( $to );
    $mail->setFrom( $settings->data()->developer );
    $host     = $this->httpRequest->getUrl()->getHost();
    $host     = preg_replace( '#[^\w.-]+#', '', isset( $host ) ? $host : php_uname( 'n' ) );
    $mail->setSubject( "ERROR - CMS IMPNET - " . $host );
    $mail->setBody( $message );
    $this->mailer->send( $mail );
  }

  /**
   * Nastavi zakladni lokalizaci
   * (je nutno zavolat pred pouzitim metod teto tridy)
   * @param string $lang Lokalizace (cs, en, ...)
   */
  public function setLang( $lang )
  {
    $this->locale = $lang;
    $this->translator->setLang( $lang );
  }

}
