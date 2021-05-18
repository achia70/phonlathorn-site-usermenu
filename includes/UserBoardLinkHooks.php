<?php
/**
 * Add a link to user's board to personal tools menu.
 */

class UserBoardLinkHooks {
	/**
	 * Return a Title for the page where the current user's sandbox is.
	 *
	 * @param Skin $skin For context
	 * @return Title|null
	 */
	private static function getUserBoardTitle( Skin $skin ) {
		$pageMsg = $skin->msg( 'userboardlink-page-name' )->inContentLanguage();
		if ( $pageMsg->isDisabled() ) {
			return null;
		}
		$username = $skin->getUser()->getName();
		return Title::makeTitleSafe( NS_SPECIAL, $pageMsg->plain() . '/' . $username );
	}

	/**
	 * Return a link descriptor for the page where the current user's sandbox is,
	 * relative to current title and in current language.
	 *
	 * @param Skin $skin For context
	 * @return array|null Link descriptor in a format accepted by PersonalUrls hook
	 */
	private static function makeUserBoardLink( Skin $skin ) {
		$currentTitle = $skin->getTitle();

		$title = self::getUserBoardTitle( $skin );
		if ( !$title ) {
			return null;
		}

		if ( $title->exists() ) {
			$href = $title->getLocalURL();
		}

		return [
			'id' => 'pt-userboard',
			'text' => $skin->msg( 'userboardlink-portlet-label' )->text(),
			'href' => $href,
			'class' => $title->exists() ? false : 'new',
			'exists' => $title->exists(),
			'active' => $title->equals( $currentTitle ),
		];
	}

	/**
	 * SkinPreloadExistence hook handler.
	 *
	 * Add the title of the page where the current user's sandbox is to link existence cache.
	 *
	 * @param Title[] &$titles
	 * @param Skin $skin
	 * @return bool true
	 */
	public static function onSkinPreloadExistence( &$titles, $skin ) {
		$title = self::getUserBoardTitle( $skin );
		if ( $title ) {
			$titles[] = $title;
		}
		return true;
	}

	/**
	 * PersonalUrls hook handler.
	 *
	 * Possibly add a link to the page where the current user's sandbox is to personal tools menu.
	 *
	 * @param array &$personalUrls
	 * @param Title &$title (unused)
	 * @param Skin $skin
	 * @return bool true
	 */
	public static function onPersonalUrls( &$personalUrls, &$title, $skin ) {
		global $wgSandboxLinkDisableAnon;
		if ( $wgSandboxLinkDisableAnon && $skin->getUser()->isAnon() ) {
			return true;
		}

		$link = self::makeUserBoardLink( $skin );
		if ( !$link ) {
			return true;
		}

		$newPersonalUrls = [];

		// Insert our link before the link to user preferences.
		// If the link to preferences is missing, insert at the end.
		foreach ( $personalUrls as $key => $value ) {
			if ( $key === 'preferences' ) {
				$newPersonalUrls['userboard'] = $link;
			}
			$newPersonalUrls[$key] = $value;
		}
		if ( !array_key_exists( 'userboard', $newPersonalUrls ) ) {
			$newPersonalUrls['userboard'] = $link;
		}

		$personalUrls = $newPersonalUrls;
		return true;
	}
}
