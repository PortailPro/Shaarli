<?php

require_once 'application/LinkFilter.php';

/**
 * Class LinkFilterTest.
 */
class LinkFilterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var LinkFilter instance.
     */
    protected static $linkFilter;

    /**
     * Instanciate linkFilter with ReferenceLinkDB data.
     */
    public static function setUpBeforeClass()
    {
        $refDB = new ReferenceLinkDB();
        self::$linkFilter = new LinkFilter($refDB->getLinks());
    }

    /**
     * Blank filter.
     */
    public function testFilter()
    {
        $this->assertEquals(
            ReferenceLinkDB::$NB_LINKS_TOTAL,
            count(self::$linkFilter->filter('', ''))
        );

        // Private only.
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter('', '', false, true))
        );

        $this->assertEquals(
            ReferenceLinkDB::$NB_LINKS_TOTAL,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TAG, ''))
        );

        $this->assertEquals(
            ReferenceLinkDB::$NB_LINKS_TOTAL,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, ''))
        );
    }

    /**
     * Filter links using a tag
     */
    public function testFilterOneTag()
    {
        $this->assertEquals(
            4,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TAG, 'web', false))
        );

        // Private only.
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TAG, 'web', false, true))
        );
    }

    /**
     * Filter links using a tag - case-sensitive
     */
    public function testFilterCaseSensitiveTag()
    {
        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TAG, 'mercurial', true))
        );

        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TAG, 'Mercurial', true))
        );
    }

    /**
     * Filter links using a tag combination
     */
    public function testFilterMultipleTags()
    {
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TAG, 'dev cartoon', false))
        );
    }

    /**
     * Filter links using a non-existent tag
     */
    public function testFilterUnknownTag()
    {
        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TAG, 'null', false))
        );
    }

    /**
     * Return links for a given day
     */
    public function testFilterDay()
    {
        $this->assertEquals(
            3,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_DAY, '20121206'))
        );
    }

    /**
     * 404 - day not found
     */
    public function testFilterUnknownDay()
    {
        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_DAY, '19700101'))
        );
    }

    /**
     * Use an invalid date format
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Invalid date format/
     */
    public function testFilterInvalidDayWithChars()
    {
        self::$linkFilter->filter(LinkFilter::$FILTER_DAY, 'Rainy day, dream away');
    }

    /**
     * Use an invalid date format
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Invalid date format/
     */
    public function testFilterInvalidDayDigits()
    {
        self::$linkFilter->filter(LinkFilter::$FILTER_DAY, '20');
    }

    /**
     * Retrieve a link entry with its hash
     */
    public function testFilterSmallHash()
    {
        $links = self::$linkFilter->filter(LinkFilter::$FILTER_HASH, 'IuWvgA');

        $this->assertEquals(
            1,
            count($links)
        );

        $this->assertEquals(
            'MediaGoblin',
            $links['20130614_184135']['title']
        );
    }

    /**
     * No link for this hash
     *
     * @expectedException LinkNotFoundException
     */
    public function testFilterUnknownSmallHash()
    {
        self::$linkFilter->filter(LinkFilter::$FILTER_HASH, 'Iblaah');
    }

    /**
     * Full-text search - no result found.
     */
    public function testFilterFullTextNoResult()
    {
        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'azertyuiop'))
        );
    }

    /**
     * Full-text search - result from a link's URL
     */
    public function testFilterFullTextURL()
    {
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'ars.userfriendly.org'))
        );
        
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'ars org'))
        );
    }

    /**
     * Full-text search - result from a link's title only
     */
    public function testFilterFullTextTitle()
    {
        // use miscellaneous cases
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'userfriendly -'))
        );
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'UserFriendly -'))
        );
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'uSeRFrIendlY -'))
        );

        // use miscellaneous case and offset
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'RFrIendL'))
        );
    }

    /**
     * Full-text search - result from the link's description only
     */
    public function testFilterFullTextDescription()
    {
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'publishing media'))
        );
        
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'mercurial w3c'))
        );
        
        $this->assertEquals(
            3,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, '"free software"'))
        );        
    }

    /**
     * Full-text search - result from the link's tags only
     */
    public function testFilterFullTextTags()
    {
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'gnu'))
        );

        // Private only.
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'web', false, true))
        );
    }

    /**
     * Full-text search - result set from mixed sources
     */
    public function testFilterFullTextMixed()
    {
        $this->assertEquals(
            3,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'free software'))
        );
    }

    /**
     * Full-text search - test exclusion with '-'.
     */
    public function testExcludeSearch()
    {
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, 'free -gnu'))
        );

        $this->assertEquals(
            6,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TEXT, '-revolution'))
        );
    }

    /**
     * Full-text search - test AND, exact terms and exclusion combined, across fields.
     */
    public function testMultiSearch()
    {
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(
                LinkFilter::$FILTER_TEXT,
                '"Free Software " stallman "read this" @website stuff'
            ))
        );

        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(
                LinkFilter::$FILTER_TEXT,
                '"free software " stallman "read this" -beard @website stuff'
            ))
        );
    }

    /**
     * Full-text search - make sure that exact search won't work across fields.
     */
    public function testSearchExactTermMultiFieldsKo()
    {
        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(
                LinkFilter::$FILTER_TEXT,
                '"designer naming"'
            ))
        );

        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(
                LinkFilter::$FILTER_TEXT,
                '"designernaming"'
            ))
        );
    }

    /**
     * Tag search with exclusion.
     */
    public function testTagFilterWithExclusion()
    {
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TAG, 'gnu -free'))
        );

        $this->assertEquals(
            6,
            count(self::$linkFilter->filter(LinkFilter::$FILTER_TAG, '-free'))
        );
    }

    /**
     * Test crossed search (terms + tags).
     */
    public function testFilterCrossedSearch()
    {
        $terms = '"Free Software " stallman "read this" @website stuff';
        $tags = 'free';
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(
                LinkFilter::$FILTER_TAG | LinkFilter::$FILTER_TEXT,
                array($tags, $terms)
            ))
        );
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(
                LinkFilter::$FILTER_TAG | LinkFilter::$FILTER_TEXT,
                array('', $terms)
            ))
        );
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(
                LinkFilter::$FILTER_TAG | LinkFilter::$FILTER_TEXT,
                array($tags, '')
            ))
        );
        $this->assertEquals(
            ReferenceLinkDB::$NB_LINKS_TOTAL,
            count(self::$linkFilter->filter(
                LinkFilter::$FILTER_TAG | LinkFilter::$FILTER_TEXT,
                ''
            ))
        );
    }
}
