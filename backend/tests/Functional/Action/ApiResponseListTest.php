<?php

namespace App\Tests\Functional\Action;

use App\Tests\Functional\AuthorizationTrait;
use App\Tests\Functional\Testcase\FixtureAwareTestCase;
use Symfony\Component\HttpFoundation\Request;

class ApiResponseListTest extends FixtureAwareTestCase
{
    use AuthorizationTrait;

    /**
     * @dataProvider urlProvider
     * @param string $url
     * @return void
     */
    public function testIfApiResponseDisplaysProperLimitWithNoLimit(string $url): void
    {
        $this->setAuthorizationToken('admin', 'admin');

        $this->client->request(Request::METHOD_GET, $url);

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertObjectHasAttribute('_limit', $content);
        $this->assertObjectHasAttribute('_data', $content);
        $this->assertObjectHasAttribute('_total', $content);
        $this->assertObjectHasAttribute('_count', $content);
        $this->assertObjectHasAttribute('_offset', $content);

        $this->assertEquals(null, $content->_offset);
        $this->assertEquals(null, $content->_limit);
    }

    /**
     * @dataProvider urlProvider
     * @param string $url
     */
    public function testIfLimitWorks(string $url): void
    {
        $this->setAuthorizationToken('admin', 'admin');

        $parameters = [
            'limit' => 1,
            'offset' => 0,
        ];

        $this->client->request(Request::METHOD_GET, $url, $parameters);

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertObjectHasAttribute('_limit', $content);
        $this->assertObjectHasAttribute('_data', $content);
        $this->assertObjectHasAttribute('_total', $content);
        $this->assertObjectHasAttribute('_count', $content);
        $this->assertObjectHasAttribute('_offset', $content);

        $this->assertEquals(0, $content->_offset);
        $this->assertEquals(1, $content->_limit);
        $this->assertCount(1, $content->_data);
    }

    /**
     * @dataProvider nestedUrlProvider
     * @param string $url
     * @param string $nestedUrl
     */
    public function testIfLimitWorksOnSubItems(string $url, string $nestedUrl): void
    {
        $this->setAuthorizationToken('admin', 'admin');

        $this->client->request(Request::METHOD_GET, $url);

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $randomItemId = $content->_data[0]->id;

        $parameters = [
            'limit' => 1,
            'offset' => 0,
        ];

        $this->client->request(Request::METHOD_GET, "$url/$randomItemId/$nestedUrl", $parameters);

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertObjectHasAttribute('_limit', $content);
        $this->assertObjectHasAttribute('_data', $content);
        $this->assertObjectHasAttribute('_total', $content);
        $this->assertObjectHasAttribute('_count', $content);
        $this->assertObjectHasAttribute('_offset', $content);

        $this->assertEquals(0, $content->_offset);
        $this->assertEquals(1, $content->_limit);
    }

    /**
     * @return array
     */
    public function nestedUrlProvider(): array
    {
        return [
            ['/api/posts', 'comments'],
            ['/api/posts', 'trends'],
            ['/api/posts', 'likes'],
            ['/api/developers', 'likes'],
        ];
    }

    /**
     * @return array
     */
    public function urlProvider(): array
    {
        return [
            ['/api/trends'],
            ['/api/posts'],
            ['/api/comments'],
            ['/api/developers'],
            ['/api/likes'],
        ];
    }
}
