<?php

namespace Victoire\Bundle\I18nBundle\Resolver;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;

/**
* A class to guess the locale form URL
*/
class LocaleResolver
{
    const PATTERNDOMAIN         = 'domain';
    const PATTERNPARAMETERINURL = 'inurl';

	protected $localePattern;
	protected $localePatternTable;
	protected $defaultLocale;
	protected $victoireLocale;
	protected $applicationLocales;

	/**
	* Constructor
	*
	* @param string $localePattern 
	* @param string $localPatternTable
	* @param string $defaultLocale
	*/
	public function __construct($localePattern, $localePatternTable, $defaultLocale, $victoireLocale, $applicationLocales) 
	{
		$this->localePattern = $localePattern;
		$this->localePatternTable = $localePatternTable;
		$this->defaultLocale = $defaultLocale;
		$this->victoireLocale = $victoireLocale;
		$this->applicationLocales = $applicationLocales;
	}

	/**
	* @param GetResponseEvent $event
	* method called on kernelRequest it sets the local in request depending on patterns
	* it also set the victoire_locale wich is the locale of the application admin
	*/
	public function onKernelRequest(GetResponseEvent $event)
    {
		if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        } else {
        	//locale
        	$request = $event->getRequest();
        	switch ($this->localePattern) {
        		case self::PATTERNDOMAIN : 
        		    $locale = $this->resolveFromDomain($request);
        		    $request->setLocale($locale);
        	        break;

        	     case self::PATTERNPARAMETERINURL : 
        		    $locale = $this->resolveAsParameterInUrl($request);
        		    $request->setLocale($locale);

        	        break;
        	    default : 
        	        break; 
        	} 

        	//victoireLocale
        	if (!$request->getSession()->get('victoire_locale')) {
        		$request->getSession()->set('victoire_locale', $this->victoireLocale);
        	}
        }
    }
    /**
    * @param Request $request
    *
    * @return string 
    *
    * resolves the locale from host
    */
	public function resolveFromDomain(Request $request) 
	{
		$host = $request->getHttpHost();

        return $this->localePatternTable[$host];
	}

	/**
    * @param Request $request
    *
    * @return string
    *
    * This method resolves the domain from locale 
    */
	public function resolveDomainForLocale($locale) 
	{
		foreach ($this->localePatternTable as $domain => $domainLocale) {
			if ($locale === $domainLocale) 
			{
				return $domain;
			}	
		}

        return 'fr';
	}

	/**
    * @param Request $request
    *
    * @return string 
	*
    * this method resolves the locale from parameters pass in the url
    */
	public function resolveAsParameterInUrl(Request $request) 
	{
		$uri = $request->getRequestUri();
		if(strstr($uri, '/app_dev.php/')) {
			$uri = str_replace('/app_dev.php/', '', $uri);
		} else {
			$uri = ltrim ($uri, '/');
		}
		$endLocalePos  = strpos($uri, '/');
		if (!empty($uri)) {
		   $endLocale = substr($uri, 0, $endLocalePos);
		   return $endLocale;
		} else {
			return $this->defaultLocale;
		}
	}
}