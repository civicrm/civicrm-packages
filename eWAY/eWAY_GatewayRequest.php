<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**************************************************************************************************************************
 * Licensed to CiviCRM under the Academic Free License version 3.0
 * Written & Contributed by Dolphin Software P/L - March 2008 
 *
 * 'eWAY_GatewayRequest.php' - Based on the standard supplied eWay sample code 'GatewayResponse.php'
 *
 * The only significant change from the original is that the 'CVN' field is uncommented,
 * unlike the distributed sample code.
 *
 * ALSO: Added a 'GetTransactionNumber' function.
 *
 **************************************************************************************************************************/
 
class GatewayRequest
{
    /**
     * @var string
     */
    public $txCustomerID = '';

    /**
     * @var int
     */
    public $txAmount = 0;

    /**
     * @var string
     */
    public $txCardholderName = '';

    /**
     * @var string
     */
    public $txCardNumber = '';

    /**
     * @var string
     */
    public $txCardExpiryMonth = '01';

    /**
     * @var string
     */
    public $txCardExpiryYear = '00';

    /**
     * @var string
     */
    public $txTransactionNumber = '';

    /**
     * @var string
     */
    public $txCardholderFirstName = '';

    /**
     * @var string
     */
    public $txCardholderLastName = '';

    /**
     * @var string
     */
    public $txCardholderEmailAddress = '';

    /**
     * @var string
     */
    public $txCardholderAddress = '';

    /**
     * @var string
     */
    public $txCardholderPostalCode = '';

    /**
     * @var string
     */
    public $txInvoiceReference = '';

    /**
     * @var string
     */
    public $txInvoiceDescription = '';

    /**
     * @var string
     */
    public $txCVN = '';

    /**
     * @var string
     */
    public $txOption1 = '';

    /**
     * @var string
     */
    public $txOption2 = '';

    /**
     * @var string
     */
    public $txOption3 = '';

    /**
     * @var string
     */
    public $txCustomerBillingCountry = '';

    /**
     * @var string
     */
    public $txCustomerIPAddress = '';

   public function __construct()
   {
      // Empty Constructor
   }

    /**
     * @return string
     */
    function GetTransactionNumber()
   {
      return $this->txTransactionNumber;
   }

    /**
     * @param $value
     */
    function EwayCustomerID($value)
   {
      $this->txCustomerID=$value;
   }

    /**
     * @param $value
     */
    function InvoiceAmount($value)
   {
      $this->txAmount=$value;
   }

    /**
     * @param $value
     */
    function CardHolderName($value)
   {
      $this->txCardholderName=$value;
   }

    /**
     * @param $value
     */
    function CardExpiryMonth($value)
   {
      $this->txCardExpiryMonth=$value;
   }

    /**
     * @param $value
     */
    function CardExpiryYear($value)
   {
      $this->txCardExpiryYear=$value;
   }

    /**
     * @param $value
     */
    function TransactionNumber($value)
   {
      $this->txTransactionNumber=$value;
   }

    /**
     * @param $value
     */
    function PurchaserFirstName($value)
   {
      $this->txCardholderFirstName=$value;
   }

    /**
     * @param $value
     */
    function PurchaserLastName($value)
   {
      $this->txCardholderLastName=$value;
   }

    /**
     * @param $value
     */
    function CardNumber($value)
   {
      $this->txCardNumber=$value;
   }

    /**
     * @param $value
     */
    function PurchaserAddress($value)
   {
      $this->txCardholderAddress=$value;
   }

    /**
     * @param $value
     */
    function PurchaserPostalCode($value)
   {
      $this->txCardholderPostalCode=$value;
   }

    /**
     * @param $value
     */
    function PurchaserEmailAddress($value)
   {
      $this->txCardholderEmailAddress=$value;
   }

    /**
     * @param $value
     */
    function InvoiceReference($value)
   {
      $this->txInvoiceReference=$value; 
   }

    /**
     * @param $value
     */
    function InvoiceDescription($value)
   {
      $this->txInvoiceDescription=$value; 
   }

    /**
     * @param $value
     */
    function CVN($value)
   {
      $this->txCVN=$value; 
   }

    /**
     * @param $value
     */
    function EwayOption1($value)
   {
      $this->txOption1=$value; 
   }

    /**
     * @param $value
     */
    function EwayOption2($value)
   {
      $this->txOption2=$value; 
   }

    /**
     * @param $value
     */
    function EwayOption3($value)
   {
      $this->txOption3=$value; 
   }

    /**
     * @param $value
     */
    function CustomerBillingCountry($value)
   {
       $this->txCustomerBillingCountry=$value; 
   }

    /**
     * @param $value
     */
    function CustomerIPAddress($value)
   {
       $this->txCustomerIPAddress=$value; 
   }

    /**
     * @return string
     */
    function ToXml()
   {
      // We don't really need the overhead of creating an XML DOM object
      // to really just concatenate a string together.

      $xml = '<ewaygateway>';
      $xml .= $this->CreateNode('ewayCustomerID',                 $this->txCustomerID);
      $xml .= $this->CreateNode('ewayTotalAmount',                $this->txAmount);
      $xml .= $this->CreateNode('ewayCardHoldersName',            $this->txCardholderName);
      $xml .= $this->CreateNode('ewayCardNumber',                 $this->txCardNumber);
      $xml .= $this->CreateNode('ewayCardExpiryMonth',            $this->txCardExpiryMonth);
      $xml .= $this->CreateNode('ewayCardExpiryYear',             $this->txCardExpiryYear);
      $xml .= $this->CreateNode('ewayTrxnNumber',                 $this->txTransactionNumber);
      $xml .= $this->CreateNode('ewayCustomerInvoiceDescription', $this->txInvoiceDescription);
      $xml .= $this->CreateNode('ewayCustomerFirstName',          $this->txCardholderFirstName);
      $xml .= $this->CreateNode('ewayCustomerLastName',           $this->txCardholderLastName);
      $xml .= $this->CreateNode('ewayCustomerEmail',              $this->txCardholderEmailAddress);
      $xml .= $this->CreateNode('ewayCustomerAddress',            $this->txCardholderAddress);
      $xml .= $this->CreateNode('ewayCustomerPostcode',           $this->txCardholderPostalCode);
      $xml .= $this->CreateNode('ewayCustomerInvoiceRef',         $this->txInvoiceReference);
      $xml .= $this->CreateNode('ewayCVN',                        $this->txCVN);
      $xml .= $this->CreateNode('ewayOption1',                    $this->txOption1);
      $xml .= $this->CreateNode('ewayOption2',                    $this->txOption2);
      $xml .= $this->CreateNode('ewayOption3',                    $this->txOption3);
      $xml .= $this->CreateNode('ewayCustomerIPAddress',          $this->txCustomerIPAddress);
      $xml .= $this->CreateNode('ewayCustomerBillingCountry',     $this->txCustomerBillingCountry);
      $xml .= '</ewaygateway>';
      
      return $xml;
   }
   
   
   /********************************************************
   * Builds a simple XML Node
   *
   * 'NodeName' is the anem of the node being created.
   * 'NodeValue' is its value
   *
   ********************************************************/
   public function CreateNode($NodeName, $NodeValue)
   {
    require_once 'XML/Util.php';

    $xml = new XML_Util();
    $node = '<' . $NodeName . '>' . $xml->replaceEntities($NodeValue) . '</' . $NodeName . '>';
    return $node;
   }
   
} // class GatewayRequest

?>
