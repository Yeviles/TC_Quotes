<?php
declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Model;

use Logiscenter\ImmutableQuote\Model\ImmutableQuote;
use Logiscenter\ImmutableQuote\Model\ImmutableQuoteCache;
use Logiscenter\ImmutableQuote\Model\ImmutableQuoteFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImmutableQuoteCacheTest extends TestCase
{
    private CacheInterface&MockObject $cache;

    private ImmutableQuoteFactory&MockObject $factory;

    private SerializerInterface&MockObject $serializer;

    private ImmutableQuoteCache $quoteCache;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->factory = $this->createMock(ImmutableQuoteFactory::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->quoteCache = new ImmutableQuoteCache($this->cache, $this->factory, $this->serializer);
    }

    public function testLoadReturnsNullWhenNothingIsCached(): void
    {
        $this->cache->method('load')->willReturn(false);

        self::assertNull($this->quoteCache->load(10));
    }

    public function testLoadHydratesEntityFromCachedData(): void
    {
        $this->cache->method('load')
            ->with('logiscenter_immutable_quote_10')
            ->willReturn('{"quote_id":10}');
        $this->serializer->method('unserialize')->with('{"quote_id":10}')->willReturn(['quote_id' => 10]);

        $entity = $this->createMock(ImmutableQuote::class);
        $entity->expects(self::once())->method('setData')->with(['quote_id' => 10]);
        $this->factory->method('create')->willReturn($entity);

        self::assertSame($entity, $this->quoteCache->load(10));
    }

    public function testLoadReturnsRequestCachedEntityWithoutHittingCacheBackend(): void
    {
        $this->cache->method('load')->willReturn('{"quote_id":10}');
        $this->serializer->method('unserialize')->willReturn(['quote_id' => 10]);
        $entity = $this->createMock(ImmutableQuote::class);
        $this->factory->method('create')->willReturn($entity);

        $this->quoteCache->load(10);

        $this->cache->expects(self::never())->method('load');
        self::assertSame($entity, $this->quoteCache->load(10));
    }

    public function testSavePersistsSerializedEntityData(): void
    {
        $entity = $this->createMock(ImmutableQuote::class);
        $entity->method('getQuoteId')->willReturn(10);
        $entity->method('getData')->willReturn(['quote_id' => 10, 'is_immutable' => 1]);
        $this->serializer->method('serialize')
            ->with(['quote_id' => 10, 'is_immutable' => 1])
            ->willReturn('{"quote_id":10,"is_immutable":1}');

        $this->cache->expects(self::once())
            ->method('save')
            ->with(
                '{"quote_id":10,"is_immutable":1}',
                'logiscenter_immutable_quote_10',
                [ImmutableQuote::CACHE_TAG],
                300
            );

        $this->quoteCache->save($entity);
    }

    public function testInvalidateRemovesEntryFromCacheBackend(): void
    {
        $this->cache->expects(self::once())->method('remove')->with('logiscenter_immutable_quote_10');
        $this->quoteCache->invalidate(10);
    }
}
