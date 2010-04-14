<?php

/**
	* Utilitary functions for calendar
 **/
class CalendarUtils
{
	/**
	*	Ajuste l'heure de dateUtc en fonction de l'ouverture
	* La dateUtc est considérée dispo
	**/
	public function adjustHour($dateUtc, $calendar) {
		$wd = date('w', $dateUtc);
		$startHour = intval($calendar[$wd]['start_hour']);
		$stopHour = intval($calendar[$wd]['stop_hour']);
		$currentHour = intval(date('H', $dateUtc));
		$currentMin = intval(date('i', $dateUtc));

		// arrondi à l'heure juste d'après
		if ($currentMin > 0) {
			$currentHour = $currentHour + 1;
			$dateUtc = mktime($currentHour, 0, 0, date('m', $dateUtc), date('d', $dateUtc), date('Y', $dateUtc));
		}
		// si on est avant l'heure de départ, on se met à l'heure de départ
		if ($currentHour < $startHour) {
			$dateUtc = mktime($startHour, 0, 0, date('m', $dateUtc), date('d', $dateUtc), date('Y', $dateUtc));
		}
		return ($dateUtc);
	}
	
	/**
	 * Ajout un délai à la date dateUtc : 0.5 jour ou 1*nb de jours 
	 * Prend en compte le calendrier & les exceptions
	**/
	public function addDelay($dateUtc, $delay, $calendar, $exceptions) {
		// on se base sur la prochaine date dispo
		$dateUtc = $this->getNextDateAvailable($dateUtc, $calendar, $exceptions);
		if (!$dateUtc)
			return (null);
			
		if ($delay == '0.5') {
			$hour = intval(date('H', $dateUtc));
			if ($hour < 12) {
				$dateUtc = mktime('14', 0, 0, date('m', $dateUtc), date('d', $dateUtc), date('Y', $dateUtc));
			} else {
				$dateUtc += 3600 * 24;
				// on remet sur 00h00 pr livrer au début du jour
				$dateUtc = mktime(0, 0, 0, date('m', $dateUtc), date('d', $dateUtc), date('Y', $dateUtc));
				$dateUtc = $this->getNextDateAvailable($dateUtc, $calendar, $exceptions);				
			}
			return ($dateUtc);
		}
		
		$deliveryDelay = intval($delay);
		while ($deliveryDelay--)
		{
			$dateUtc += 3600 * 24;
			$dateUtc = mktime(0, 0, 0, date('m', $dateUtc), date('d', $dateUtc), date('Y', $dateUtc));	
			$dateUtc = $this->getNextDateAvailable($dateUtc, $calendar, $exceptions);							
		}
		return ($dateUtc);
	}
	
	/**
	 *	Renvoie la prochaine journée disponible (soit $dateUtc, soit la prochaine à 00h00)
	 *	Si aucune ds les 15j, renvoie NULL
	 **/
	public function getNextDateAvailable($dateUtc, $calendar, $exceptions)
	{
		// si dateUtc est dispo, retourne $dateUtc
		if ($this->isDateAvailable($dateUtc, $calendar, $exceptions))
			return ($dateUtc);
		
		$loopcount = 0;
		// on positionne au début de journée
		$dateUtc = mktime(0, 0, 0, date('m', $dateUtc), date('d', $dateUtc), date('Y', $dateUtc));
		// on boucle pour trouver une journée dispo (si ds les 15j, y a pas : on laisse tomber
		do 
		{
			$dateUtc += 3600 * 24;
			$isDateFree = $this->isDateAvailable($dateUtc, $calendar, $exceptions);
			$loopcount++;	
		} 
		while (!$isDateFree && ($loopcount < 15) );
		if ($isDateFree)
			return ($dateUtc);			
		return (NULL);	
	}

	/**
	 * Returns if $dateUtc is available (orderable date)
	 **/
	public function isDateAvailable($dateUtc, $calendar, $exceptions)
	{
		// jour ferié ?
		$mCalDate = date("d/m/Y", $dateUtc);
		if (in_array($mCalDate, $exceptions))
			return (false);
		// jour fermé ?
		$wd = date('w', $dateUtc);
		if (!isset($calendar[$wd]))
			return (false);
			
		// on arrondit à l'heure suivante & on regarde si on est avant la fermeture
		$stopHour = intval($calendar[$wd]['stop_hour']);
		$currentHour = intval(date('H', $dateUtc));
		$currentMin = intval(date('i', $dateUtc));		
		if ($currentMin > 0)
			$currentHour = $currentHour + 1;
		// avant l'heure de fermeture ?
		if ($currentHour <= $stopHour)
			return (true);
		else
			return (false);			
	}

}

?>