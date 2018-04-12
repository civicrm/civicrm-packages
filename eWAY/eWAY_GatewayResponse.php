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
 * 'eWAY_GatewayResponse.php' - Loosley based on the standard supplied eWay sample code 'GatewayResponse.php'
 *
 * The 'simplexml_load_string' has been removed as it was causing major issues
 * with Drupal V5.7 / CiviCRM 1.9 installtion's Home page. 
 * Filling the Home page with "Warning: session_start() [function.session-start]: Node no longer exists in ..." messages
 *
 * Found web reference indicating 'simplexml_load_string' was a probable cause.
 * As soon as 'simplexml_load_string' was removed the problem fixed itself.
 *
 * Additionally the '$txStatus' var has been set as a string rather than a boolean.
 * This is because the returned $params['trxn_result_code'] is in fact a string and not a boolean.
 **************************************************************************************************************************/
 
class GatewayResponse
{
    /**
     * @var int
     */
    public $txAmount = 0;
    /**
     * @var string
     */
    public $txTransactionNumber = '';
    /**
     * @var string
     */
    public $txInvoiceReference = '';
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
    public $txStatus = '';
    /**
     * @var string
     */
    public $txAuthCode = '';
    /**
     * @var string
     */
    public $txError = '';
    /**
     * @var string
     */
    public $txBeagleScore = '';

	public function __construct()
	{
	   // Empty Constructor
    }

    /**
     * @param $Xml
     */
    public function ProcessResponse($Xml)
	{
#####################################################################################
#                                                                                   #
#      $xtr = simplexml_load_string($Xml) or die ("Unable to load XML string!");    #
#                                                                                   #
#      $this->txError             = $xtr->ewayTrxnError;                            #
#      $this->txStatus            = $xtr->ewayTrxnStatus;                           #
#      $this->txTransactionNumber = $xtr->ewayTrxnNumber;                           #
#      $this->txOption1           = $xtr->ewayTrxnOption1;                          #
#      $this->txOption2           = $xtr->ewayTrxnOption2;                          #
#      $this->txOption3           = $xtr->ewayTrxnOption3;                          #
#      $this->txAmount            = $xtr->ewayReturnAmount;                         #
#      $this->txAuthCode          = $xtr->ewayAuthCode;                             #
#      $this->txInvoiceReference  = $xtr->ewayTrxnReference;                        #
#                                                                                   #
#####################################################################################

      $this->txError             = $this->GetNodeValue('ewayTrxnError', $Xml);
      $this->txStatus            = $this->GetNodeValue('ewayTrxnStatus', $Xml);
      $this->txTransactionNumber = $this->GetNodeValue('ewayTrxnNumber', $Xml);
      $this->txOption1           = $this->GetNodeValue('ewayTrxnOption1', $Xml);
      $this->txOption2           = $this->GetNodeValue('ewayTrxnOption2', $Xml);
      $this->txOption3           = $this->GetNodeValue('ewayTrxnOption3', $Xml);
      $amount                    = $this->GetNodeValue('ewayReturnAmount', $Xml);
      $this->txAuthCode          = $this->GetNodeValue('ewayAuthCode', $Xml);
      $this->txInvoiceReference  = $this->GetNodeValue('ewayTrxnReference', $Xml);
      $this->txBeagleScore       = $this->GetNodeValue('ewayBeagleScore', $Xml);
      $this->txAmount = (int) $amount;
   }

    /************************************************************************
     * Simple function to use in place of the 'simplexml_load_string' call.
     *
     * It returns the NodeValue for a given NodeName
     * or returns and empty string.
     ***********************************************************************
     *
     * @param $NodeName
     * @param $strXML
     *
     * @return bool|string
     */
   public function GetNodeValue($NodeName, &$strXML)
   {
      $OpeningNodeName = '<' . $NodeName . '>';
      $ClosingNodeName = '</' . $NodeName . '>';
      
      $pos1 = stripos($strXML, $OpeningNodeName);
      $pos2 = stripos($strXML, $ClosingNodeName);
      
      if ( ($pos1 === false) || ($pos2 === false) ) {
          return '';
      }
         
      $pos1 += strlen($OpeningNodeName);
      $len   = $pos2 - $pos1;

      $return = substr($strXML, $pos1, $len);                                       
      
      return $return;
   }

    /**
     * @return string
     */
    public function TransactionNumber()
   {
      return $this->txTransactionNumber; 
   }

    /**
     * @return string
     */
    public function InvoiceReference()
   {
      return $this->txInvoiceReference; 
   }

    /**
     * @return string
     */
    public function Option1()
   {
      return $this->txOption1; 
   }

    /**
     * @return string
     */
    public function Option2()
   {
      return $this->txOption2; 
   }

    /**
     * @return string
     */
    public function Option3()
   {
      return $this->txOption3; 
   }

    /**
     * @return string
     */
    public function AuthorisationCode()
   {
      return $this->txAuthCode; 
   }

    /**
     * @return string
     */
    public function Error()
   {
      return $this->txError; 
   }

    /**
     * @return int
     */
    public function Amount()
   {
      return $this->txAmount; 
   }

    /**
     * @return string
     */
    public function Status()
   {
      return $this->txStatus;
   }

    /**
     * @return string
     */
    public function BeagleScore ()
   {
       return $this->txBeagleScore ;
   }
}


