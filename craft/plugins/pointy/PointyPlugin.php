<?php

namespace Craft;

class PointyPlugin extends BasePlugin {

	function getName()
	{
		return Craft::t('Pointy');
	}

	function getVersion()
	{
		return '1.0';
	}

	function getDeveloper()
	{
		return 'Iain Urquhart';
	}

	function getDeveloperUrl()
	{
		return 'http://danielezanfardino.com';
	}

    /**
     * Returns the plugin’s release feed.
     *
     * @return JSON
     */
    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/dzanfardino/Craft-Pointy/master/releases.json';
    }

}