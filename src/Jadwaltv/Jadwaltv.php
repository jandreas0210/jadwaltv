<?php


/**
* -------------------------------------------------------------------
* Class Jadwal TV 
* -------------------------------------------------------------------
* Library ini digunakan untuk menampilkan jadwal siaran TV yg tayang 
* pada hari ini, Channel hanya tersedia dari channel indonesia.
* Source Di Grab dari web www.doktv.com
* ____________________________________________________________________
*
* @author Muhamad Ridwan
* @since 2015
*/


namespace Ridwanskaterock;


class Jadwaltv
{
	private $URLSource = 'www.doktv.com';

	private $dataJadwalTv;

	private $channelList = [
		'transtv', 
		'antv', 
		'globaltv', 
		'indosiar', 
		'metrotv', 
		'mnctv', 
		'nettv',
		'rtv',
		'sctv',
		'trans7',
		'tvone',
		'kompastv',
	];

	public $defaultChannel = 'transtv';

	private $channelName;

	private $resultContentJadwalHtml;

	private $resultContentJadwalArray;

	private $responseFormat = 'html';

	/**
	* @param String $channelName
	*/
	public function setChannel($channelName = NULL)
	{
		if($channelName)
		{
			$this->channelName = $channelName;
		}

		return $this;
	}

	/**
	* @return String
	*/
	public function getActiveChannel()
	{
		return $this->channelName;
	}

	/**
	* @param String $format
	*/
	public function setDataFormat($format = NULL)
	{
		if($format)
		{
			$this->responseFormat = $format;
		}

		return $this;
	}

	/**
	* @return String
	*/
	public function getDataFormat()
	{
		return $this->responseFormat;
	}

	/**
	* @return String
	*/
	public function getChannelLIst()
	{
		return $this->channelList;
	}

	/**
	* @param String $channelName
	* @param String $format
	*
	* @return String | Array | XML | JSON
	*/
	public function loadJadwalTv($channelName = NULL, $format = NULL)
	{
		self::setChannel($channelName);


		$URL = 'www.dokitv.com/jadwal-' . $this->channelName;

		ob_start();
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_RETURN_TRANSFER, TRUE);

		$response = curl_exec($ch);


		$buffer = ob_get_contents();
		@ob_end_clean();

		$table = explode('<table id="tabeljadwaltv">', $buffer);

		if(!isset($table[1]))
		{
			return FALSE;
		}


		$tableContent = explode('</table>', $table[1]);

		if(!isset($tableContent[0]))
		{
			return FALSE;
		}

		$this->resultContentJadwalHtml = $tableContent[0]; 
		$this->setDataFormat($format);

		return $this->dataFormatter();
	}

	/**
	* @param String $format
	*
	* @return String | Array | XML | JSON
	*/
	public function loadSemuaJadwalTv($format = NULL)
	{
		$outs = NULL;
		$this->setDataFormat($format);

		foreach($this->channelList as $channelName)
		{
			$outs .= self::loadJadwalTv($channelName, $this->format);
		}

		return $outs;
	}

	/**
	* @return Array
	*/
	public function arrayFormatter()
	{
		$resultArr = [];

		$rowContent = explode('<tr class="even"><th colspan="2">', $this->resultContentJadwalHtml);
		$rowContent2 = explode('</th>', $rowContent[1]);

		$resultArr['title'] = $rowContent2[0];

		$jadwalContent = explode('</tr>', $this->resultContentJadwalHtml);

		foreach($jadwalContent as $singleContent)
		{
			$singleContent = strip_tags($singleContent);

			$jamTayang = substr($singleContent, 0, 5);
			$event = substr($singleContent, 5);

			if(!empty($jamTayang) AND !empty($event))
			{
				$resultArr['jadwal'][] = [
					'jam' 	=> $jamTayang, 
					'event' => $event
				];
			}
		}

		unset($resultArr['jadwal'][0]);

		return $resultArr;
	}

	/**
	* @param String $format
	* 
	* @return String | Array | XML | JSON
	*/
	public function dataFormatter($format = NULL)
	{
		$this->setDataFormat($format);

		if($this->responseFormat == 'html')
		{
			return "<html><table>" . $this->resultContentJadwalHtml . "</table></html>";
		}
		elseif($this->responseFormat == 'array')
		{
			return $this->arrayFormatter();
		}
		elseif($this->responseFormat == 'json')
		{
			return json_encode($this->arrayFormatter());
		}
		elseif($this->responseFormat == 'xml')
		{
			return xmlrpc_encode($this->arrayFormatter());
		}
		else
		{
			return "<html><table>" . $this->resultContentJadwalHtml . "</table></html>";
		}
	}

}
