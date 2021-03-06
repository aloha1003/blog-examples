<?php

require_once dirname( __FILE__ ) . '/visitor.php';
require_once dirname( __FILE__ ) . '/xml_test_case.php';

class qaPersonVisitorTest extends qaXmlTestCase
{
    protected $domDocument;

    protected $rootElement;

    protected function setUp()
    {
        $this->domDocument = new DOMDocument( '1.0', 'utf-8' );
        $this->rootElement = $this->domDocument->appendChild(
            $this->domDocument->createElement(
                'root'
            )
        );
    }

    protected function getDomDocument()
    {
        return $this->domDocument;
    }

    protected function getDomRootElement()
    {
        return $this->rootElement;
    }

    protected function getPersonFixture()
    {
        $person = new qaPerson(
            'My Last Name',
            'Some First Name'
        );
        $person->setGender( qaPerson::GENDER_FEMALE );
        $person->setDateOfBirth(
            new DateTime( '2000-01-01 00:00:00+00:00' )
        );

        return $person;
    }

    public function testVisitPersonNaive()
    {
        $person = $this->getPersonFixture();

        $expDom = new DOMDocument( '1.0', 'utf-8' );
        $expRoot = $expDom->appendChild(
            $expDom->createElement( 'root' )
        );
        $expPersonElem = $expRoot->appendChild(
            $expDom->createElement( 'Person' )
        );
        $expPersonElem->appendChild(
            $expDom->createElement(
                'LastName',
                $person->getLastName()
            )
        );
        $expPersonElem->appendChild(
            $expDom->createElement(
                'FirstName',
                $person->getFirstName()
            )
        );
        $expPersonElem->appendChild(
            $expDom->createElement(
                'Gender',
                $person->getGender()
            )
        );
        $expPersonElem->appendChild(
            $expDom->createElement(
                'DateOfBirth',
                $person->getDateOfBirth()->format( 'Y-m-d' )
            )
        );

        $visitor = new qaPersonVisitor( $this->getDomRootElement() );
        $visitor->visitPerson( $person );

        $this->assertEquals(
            $expDom,
            $this->getDomDocument()
        );
    }

    public function testVisitPersonCompareFile()
    {
        $person = $this->getPersonFixture();

        $visitor = new qaPersonVisitor( $this->getDomRootElement() );
        $visitor->visitPerson( $person );
        
        $this->assertXmlStringEqualsXmlFile(
            'data/' . __CLASS__ . '__' . __FUNCTION__ . '.xml',
            $this->getDomDocument()->saveXml()
        );
    }

    public function testVisitPersonSelectCSS()
    {
        $person = $this->getPersonFixture();

        $visitor = new qaPersonVisitor( $this->getDomRootElement() );
        $visitor->visitPerson( $person );

        $this->assertSelectCount(
            'Person',
            1,
            $this->getDomDocument(),
            'Invalid number of Person elements',
            false
        );

        $this->assertSelectEquals(
            'Person > FirstName',
            $person->getFirstName(),
            1,
            $this->getDomDocument(),
            'Invalid content of FirstName element',
            false
        );
        $this->assertSelectEquals(
            'Person > LastName',
            $person->getLastName(),
            1,
            $this->getDomDocument(),
            'Invalid content of LastName element',
            false
        );
        $this->assertSelectEquals(
            'Person > Gender',
            $person->getGender(),
            1,
            $this->getDomDocument(),
            'Invalid content of Gender element',
            false
        );
        $this->assertSelectEquals(
            'Person > DateOfBirth',
            $person->getDateOfBirth()->format( 'Y-m-d' ),
            1,
            $this->getDomDocument(),
            'Invalid content of DateOfBirth element',
            false
        );
    }

    public function testVisitPersonTag()
    {
        $person = $this->getPersonFixture();

        $visitor = new qaPersonVisitor( $this->getDomRootElement() );
        $visitor->visitPerson( $person );
        
        $this->assertTag(
            array(
                'tag' => 'Person',
                'child' => array(
                    'tag'     => 'LastName',
                    'content' => $person->getLastName(),
                ),
            ),
            $this->getDomDocument(),
            'Incorrect LastName tag',
            false
        );
        
        $this->assertTag(
            array(
                'tag' => 'Person',
                'child' => array(
                    'tag'     => 'FirstName',
                    'content' => $person->getFirstName(),
                ),
            ),
            $this->getDomDocument(),
            'Incorrect FirstName tag',
            false
        );
        
        $this->assertTag(
            array(
                'tag' => 'Person',
                'child' => array(
                    'tag'     => 'Gender',
                    'content' => (string) $person->getGender(),
                ),
            ),
            $this->getDomDocument(),
            'Incorrect Gender tag',
            false
        );
        
        $this->assertTag(
            array(
                'tag' => 'Person',
                'child' => array(
                    'tag'     => 'DateOfBirth',
                    'content' => $person->getDateOfBirth()->format( 'Y-m-d' ),
                ),
            ),
            $this->getDomDocument(),
            'Incorrect DateOfBirth tag',
            false
        );
    }

    public function testVisitPersonXpathExtensive()
    {
        $person = $this->getPersonFixture();

        $visitor = new qaPersonVisitor( $this->getDomRootElement() );
        $visitor->visitPerson( $person );

        $this->assertXpathMatch(
            1,
            'count(/root/Person)',
            'Incorrect number of Person elements.'
        );

        $this->assertXpathMatch(
            $person->getLastName(),
            'string(/root/Person/LastName)',
            'Incorrect or missing LastName element.'
        );
        $this->assertXpathMatch(
            $person->getFirstName(),
            'string(/root/Person/FirstName)',
            'Incorrect or missing FirstName element.'
        );
        $this->assertXpathMatch(
            $person->getGender(),
            'string(/root/Person/Gender)',
            'Incorrect or missing Gender element.'
        );
        $this->assertXpathMatch(
            $person->getDateOfBirth()->format( 'Y-m-d' ),
            'string(/root/Person/DateOfBirth)',
            'Incorrect or missing DateOfBirth element.'
        );
    }

    public function testVisitPersonXpathShort()
    {
        $person = $this->getPersonFixture();

        $visitor = new qaPersonVisitor( $this->getDomRootElement() );
        $visitor->visitPerson( $person );

        $this->assertXpathMatch(
            1,
            sprintf( 'count(/root/Person['
                . 'LastName = "%s" and '
                . 'FirstName = "%s" and '
                . 'Gender = "%s" and '
                . 'DateOfBirth = "%s"])',
                $person->getLastName(),
                $person->getFirstName(),
                $person->getGender(),
                $person->getDateOfBirth()->format( 'Y-m-d' )
            ),
            'Mismatching XPath.'
        );
    }
}

?>
