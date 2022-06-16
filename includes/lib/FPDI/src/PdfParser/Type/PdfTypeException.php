<?php
/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\Fpdi\PdfParser\Type;

use setasign\Fpdi\PdfParser\PdfParserException;

/**
 * Exception class for pdf type classes
 *
 * @package setasign\Fpdi\PdfParser\Type
 */
class PdfTypeException extends PdfParserException {

	/**
	 * Int
	 *
	 * @var int
	 */
	const NO_NEWLINE_AFTER_STREAM_KEYWORD = 0x0601;
}
