<?php
require_once __DIR__ . '/IpgBaseTest.php';

use Payments\Payments;

class MmrpPurchaseTest extends IpgBaseTest
{

  public function setUp()
  {
    parent::$MERCHANT_ID = "170777";
    parent::$PASSWORD = "56789";
    parent::$BRAND_ID = "1707770000";
    parent::$AMOUNT = 35;
    parent::$COUNTRY = "MX";
    parent::$CURRENCY = "MXN";
    parent::$CARD_NUMBER = "4111111111111111";
    parent::setUp();
  }

  /**
   * For MX Banamex merchants only without contract number
   */
  public function testPurchaseFailure()
  {
    $merchantTxId = 'TESTCASE_' . time();
    $sessionToken =  $this->getCardTokenHelper();

    $tokenize = $this->payments->purchase();
    $tokenize->allowOriginUrl(parent::$FAKE_HOST)
      ->brandId(parent::$BRAND_ID)
      ->merchantNotificationUrl(parent::$FAKE_HOST)
      ->paymentSolutionId(parent::$PAYMENT_SOLUTION_ID)
      ->channel(Payments::CHANNEL_ECOM)
      ->amount(parent::$AMOUNT)
      ->merchantTxId($merchantTxId)
      ->country(parent::$COUNTRY)
      ->currency(parent::$CURRENCY)
      ->specinCreditCardCVV('888')
      ->specinCreditCardToken($sessionToken->cardToken)
      //this is mandatory for payment cards method(paymentSolutionId=500), otherwise 'General error found during NVP transaction' occurs.
      ->customerId($sessionToken->customerId);
    $result = $tokenize->execute();
    parent::logResult($result);

    $this->assertEquals("Payments\ResponseError", get_class($result));
    $this->assertEquals("failure", $result->result);
  }
  /**
   * pay with contractNumber
   */
  public function testPurchaseSuccess()
  {
    $merchantTxId = 'TESTCASE_' . time();
    $sessionToken =  $this->getCardTokenHelper();

    $tokenize = $this->payments->purchase();
    $tokenize->allowOriginUrl(parent::$FAKE_HOST)
      ->brandId(parent::$BRAND_ID)
      ->merchantNotificationUrl(parent::$FAKE_HOST)
      ->paymentSolutionId(parent::$PAYMENT_SOLUTION_ID)
      ->channel(Payments::CHANNEL_ECOM)
      ->amount(parent::$AMOUNT)
      ->merchantTxId($merchantTxId)
      ->country(parent::$COUNTRY)
      ->currency(parent::$CURRENCY)
      ->specinCreditCardCVV('888')
      ->specinCreditCardToken($sessionToken->cardToken)
      //mmrp parameters
      ->cardOnFileType('First')
      ->mmrpBillPayment('Recurring')
      ->mmrpCustomerPresent("BillPayment")
      ->mmrpExistingDebt("NotExistingDebt")
      ->mmrpContractNumber("22564")
      ->mmrpCurrentInstallmentNumber(1)
      //this is mandatory for payment cards method(paymentSolutionId=500), otherwise 'General error found during NVP transaction' occurs.
      ->customerId($sessionToken->customerId);
    $result = $tokenize->execute();
    // parent::logResult($result);

    $this->assertEquals("Payments\ResponseSuccess", get_class($result));
    $this->assertEquals("redirection", $result->result);
  }
}
