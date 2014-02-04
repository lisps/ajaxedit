<?php
/**
 * @group plugin_ajaxedit
 * @group plugins
 */
class plugin_ajax_helper_test extends DokuWikiTest {

    private $h = null;
    public function setup() {
        $this->pluginsEnabled[] = 'ajaxedit';
        parent::setup();
        $this->h = plugin_load('helper','ajaxedit');
    }

    public function test_get_wikiFile() {
        global $INFO;
        $_POST['pageid'] = 'mailinglist';
        $_POST['lastmod'] = @filemtime(wikiFn('mailinglist'));
        $wiki = $this->h->getWikiPage();
        $this->assertContains('Netiquette',$wiki);
    }
    
    public function test_get_wikiFile_oldlastmod() {
        global $INFO;
        $_POST['pageid'] = 'mailinglist';
        $_POST['lastmod'] = @filemtime(wikiFn('mailinglist')) -1;

        ob_start();
        $this->h->getWikiPage();
        $wiki = ob_get_contents();
        ob_end_clean();
        $this->assertContains('The page has just been edited',$wiki);
    }
}
