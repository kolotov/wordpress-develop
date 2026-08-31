<?php

/**
 * Preserves WordPress' historical per-test coverage metadata on PHPUnit 13.
 *
 * PHPUnit 13 only permits Covers* attributes on test classes. WordPress has long used method-level
 * coverage annotations, including different targets within the same test class. Promoting those
 * targets to the class level broadens every test to the union of all targets and weakens strict
 * coverage checks. Splitting test classes would change fixtures, static state, dependencies and
 * test identities, so the PHPUnit metadata parser is decorated instead.
 */

#[Attribute( Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE )]
final readonly class WP_PHPUnit_Covers {
	public const TARGET_CLASS    = 'class';
	public const TARGET_FUNCTION = 'function';
	public const TARGET_METHOD   = 'method';

	/**
	 * @param 'class'|'function'|'method' $type   Coverage target type.
	 * @param non-empty-string            $target Class or function name.
	 * @param non-empty-string|null       $method Method name for method targets.
	 */
	public function __construct(
		public string $type,
		public string $target,
		public ?string $method = null,
	) {}
}

/**
 * Adds WP_PHPUnit_Covers metadata to PHPUnit's normal attribute parser.
 */
final readonly class WP_PHPUnit_Coverage_Metadata_Parser implements PHPUnit\Metadata\Parser\Parser {
	public function __construct(
		private PHPUnit\Metadata\Parser\Parser $parser,
	) {}

	public function forClass( string $className ): PHPUnit\Metadata\MetadataCollection {
		return $this->parser->forClass( $className );
	}

	public function forMethod( string $className, string $methodName ): PHPUnit\Metadata\MetadataCollection {
		$metadata               = array_map( $this->normalize_coverage_metadata( ... ), $this->parser->forMethod( $className, $methodName )->asArray() );
		$class_coverage_targets = array();
		$method                 = new ReflectionMethod( $className, $methodName );

		foreach ( $this->parser->forClass( $className ) as $class_metadata ) {
			$key = $this->coverage_target_key( $class_metadata );

			if ( null !== $key ) {
				$class_coverage_targets[ $key ] = true;
			}
		}

		foreach ( $method->getAttributes( WP_PHPUnit_Covers::class ) as $attribute ) {
			$covers = $attribute->newInstance();

			switch ( $covers->type ) {
				case WP_PHPUnit_Covers::TARGET_CLASS:
					$method_metadata = PHPUnit\Metadata\Metadata::coversClass( $covers->target );
					break;
				case WP_PHPUnit_Covers::TARGET_FUNCTION:
					$method_metadata = PHPUnit\Metadata\Metadata::coversFunction( $covers->target );
					break;
				case WP_PHPUnit_Covers::TARGET_METHOD:
					if ( null === $covers->method || '' === $covers->method ) {
						throw new InvalidArgumentException( 'Method coverage metadata requires a method name.' );
					}
					$method_metadata = $this->normalize_coverage_metadata(
						PHPUnit\Metadata\Metadata::coversMethod( $covers->target, $covers->method ),
					);
					break;
				default:
					throw new InvalidArgumentException( sprintf( 'Unsupported coverage target type "%s".', $covers->type ) );
			}

			if ( ! isset( $class_coverage_targets[ $this->coverage_target_key( $method_metadata ) ] ) ) {
				$metadata[] = $method_metadata;
			}
		}

		return PHPUnit\Metadata\MetadataCollection::fromArray( $metadata );
	}

	private function normalize_coverage_metadata( PHPUnit\Metadata\Metadata $metadata ): PHPUnit\Metadata\Metadata {
		if ( ! $metadata->isCoversMethod() ) {
			return $metadata;
		}

		$class_name  = $metadata->className();
		$method_name = $metadata->methodName();
		if ( ! method_exists( $class_name, $method_name ) ) {
			return $metadata;
		}

		$reflection      = new ReflectionMethod( $class_name, $method_name );
		$declaring_class = $reflection->getDeclaringClass()->getName();
		if ( $declaring_class === $class_name ) {
			return $metadata;
		}

		return PHPUnit\Metadata\Metadata::coversMethod( $declaring_class, $method_name );
	}

	private function coverage_target_key( PHPUnit\Metadata\Metadata $metadata ): ?string {
		if ( $metadata->isCoversClass() ) {
			return 'class:' . $metadata->className();
		}

		if ( $metadata->isCoversFunction() ) {
			return 'function:' . $metadata->functionName();
		}

		if ( $metadata->isCoversMethod() ) {
			return 'method:' . $metadata->className() . '::' . $metadata->methodName();
		}

		return null;
	}

	public function forClassAndMethod( string $className, string $methodName ): PHPUnit\Metadata\MetadataCollection {
		return $this->forClass( $className )->mergeWith( $this->forMethod( $className, $methodName ) );
	}
}

$phpunit_metadata_parser   = PHPUnit\Metadata\Parser\Registry::parser();
$phpunit_metadata_registry = new ReflectionClass( PHPUnit\Metadata\Parser\Registry::class );
$phpunit_metadata_instance = $phpunit_metadata_registry->getProperty( 'instance' );
$phpunit_metadata_instance->setValue( null, new WP_PHPUnit_Coverage_Metadata_Parser( $phpunit_metadata_parser ) );
unset( $phpunit_metadata_instance, $phpunit_metadata_registry, $phpunit_metadata_parser );
