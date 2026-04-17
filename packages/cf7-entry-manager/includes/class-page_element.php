<?php
/**
 * @package feryardiant/wpcf7-entry-manager
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

namespace CF7_EntryManager;

use Closure;
use WPCF7_HTMLFormatter;

/**
 * Page_Element class.
 *
 * This class provides a fluent interface for generating HTML elements using WPCF7_HTMLFormatter.
 *
 * @template T of Page_Element
 *
 * // Grouping & Text
 * @method static<T> div(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> p(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> span(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> br(array $atts = [])
 * @method static<T> wbr(array $atts = [])
 * @method static<T> hr(array $atts = [])
 *
 * // Sectioning
 * @method static<T> article(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> section(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> nav(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> aside(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> header(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> footer(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> main(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> address(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> h1(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> h2(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> h3(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> h4(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> h5(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> h6(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> hgroup(array $atts = [], \Closure(T)|string $child = null)
 *
 * // Lists
 * @method static<T> ul(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> ol(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> menu(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> li(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> dl(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> dt(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> dd(array $atts = [], \Closure(T)|string $child = null)
 *
 * // Tables
 * @method static<T> table(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> caption(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> colgroup(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> col(array $atts = [])
 * @method static<T> thead(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> tbody(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> tfoot(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> tr(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> th(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> td(array $atts = [], \Closure(T)|string $child = null)
 *
 * // Forms
 * @method static<T> form(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> fieldset(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> legend(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> label(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> input(array $atts = [])
 * @method static<T> button(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> select(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> optgroup(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> option(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> textarea(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> datalist(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> output(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> progress(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> meter(array $atts = [], \Closure(T)|string $child = null)
 *
 * // Inline Formatting
 * @method static<T> a(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> strong(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> b(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> em(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> i(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> u(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> s(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> small(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> mark(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> sub(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> sup(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> abbr(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> dfn(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> cite(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> q(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> ruby(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> rt(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> rp(array $atts = [], \Closure(T)|string $child = null)
 *
 * // Inline Tech & Data
 * @method static<T> data(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> time(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> code(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> kbd(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> samp(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> var(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> bdi(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> bdo(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> ins(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> del(array $atts = [], \Closure(T)|string $child = null)
 *
 * // Figures & Interactive
 * @method static<T> figure(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> figcaption(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> details(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> summary(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> dialog(array $atts = [], \Closure(T)|string $child = null)
 *
 * // Media & Embedded
 * @method static<T> img(array $atts = [])
 * @method static<T> picture(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> video(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> audio(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> source(array $atts = [])
 * @method static<T> track(array $atts = [])
 * @method static<T> iframe(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> canvas(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> map(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> area(array $atts = [])
 * @method static<T> object(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> param(array $atts = [])
 * @method static<T> embed(array $atts = [])
 *
 * // Miscellaneous
 * @method static<T> pre(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> blockquote(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> noscript(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> template(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> slot(array $atts = [], \Closure(T)|string $child = null)
 * @method static<T> base(array $atts = [])
 */
final class Page_Element {
	private WPCF7_HTMLFormatter $formatter;

	private array $known_elements = array();

	private array $ignored_elements = array(
		WPCF7_HTMLFormatter::placeholder_block,
		WPCF7_HTMLFormatter::placeholder_inline,
		'html', 'head', 'title', 'link', 'meta',
		'body', 'script', 'style', 'keygen'
	);

	private bool $within_element = false;

	public function __construct( array $option ) {
		if ( array_key_exists( 'allowed_html', $option ) ) {
			$option['allowed_html'] = array_merge( wpcf7_kses_allowed_html(), $option['allowed_html'] );
		}

		$this->formatter = new WPCF7_HTMLFormatter( $option );

		$known_elements = array_filter(
			array_merge(
				WPCF7_HTMLFormatter::void_elements,
				WPCF7_HTMLFormatter::p_parent_elements,
				WPCF7_HTMLFormatter::p_nonparent_elements,
				WPCF7_HTMLFormatter::p_child_elements,
				WPCF7_HTMLFormatter::br_parent_elements,
			),
			fn( string $element ) => ! in_array( $element, $this->ignored_elements )
		);

		$this->known_elements = array_unique( $known_elements );
	}

	public function __call( string $method, array $args = array() ): static
	{
		if ( ! in_array( $method, $this->known_elements ) ) {
			throw new \BadMethodCallException( sprintf(
				'Call to undefined method: %s::%s()',
				__CLASS__, $method
			) );
		}

		$atts = $args[0] ?? $args['atts'] ?? array();

		if ( ! is_array( $atts ) ) {
			throw new \TypeError( sprintf(
				'%s::%s(): Argument #1 ($atts) must be of type array, %s given',
				__CLASS__, $method, gettype( $atts )
			) );
		}

		$this->formatter->append_start_tag( $method, $atts );

		if ( in_array( $method, WPCF7_HTMLFormatter::void_elements ) ) {
			return $this;
		}

		$child = $args[1] ?? $args['child'] ?? null;

		if ( null !== $child ) {
			if ( is_string( $child )) {
				$this->formatter->append_preformatted( $child );
			} elseif ( $child instanceof Closure ) {
				$child_callback = new \ReflectionFunction( $child );

				$this->within_element = true;

				$return = $child_callback->invoke( $this );

				if ( is_string( $return ) ) {
					$this->formatter->append_preformatted( $return );
				}

				$this->within_element = false;
			} else {
				throw new \TypeError( sprintf(
					'%s::%s(): Argument #2 ($child) must be of type Closure|string, %s given',
					__CLASS__, $method, gettype( $child )
				) );
			}
		}

		$this->formatter->end_tag( $method );

		$comment = implode( '', array_filter( array(
			( ! empty( $atts['id'] ?? null ) ? '#' . $atts['id'] : null ),
			( ! empty( $atts['class'] ?? null ) ? '.' . explode( ' ', $atts['class'] )[0] : null ),
		) ) );

		if ( ! empty( $comment ) ) {
			$this->formatter->append_comment("<!-- /{$comment} -->");
		}

		return $this;
	}

	public function whitespace(): static {
		$this->formatter->append_preformatted( ' ' );

		return $this;
	}

	/**
	 * @param 'br'|'div'|'span' $mode
	 */
	public function clear( string $mode = 'br' ): static {
		if ( ! in_array( $mode, array( 'br', 'div', 'span' ) ) ) {
			throw new \InvalidArgumentException( sprintf(
				'%s::clear(): Argument #1 ($mode) must be one of "br", "div", or "span", %s given',
				__CLASS__, $mode
			) );
		}

		$this->formatter->append_start_tag( $mode, array( 'class' => 'clear' ) );

		if ( $mode !== 'br' ) {
			$this->formatter->append_end_tag( $mode );
		}

		return $this;
	}

	public function call( \Closure $callback, mixed ...$params ): static {
		$this->formatter->call_user_func( $callback, ...$params );

		return $this;
	}

	public function call_when( bool|callable $condition, \Closure $met, mixed ...$params ): static {
		if ( is_callable( $condition ) ) {
			$condition = call_user_func( $condition );
		}

		if ( $condition ) {
			return $this->call( $met, ...$params );
		}

		return $this;
	}

	/**
	 * @param bool|Closure $condition
	 * @param Closure(T) $met
	 * @param ?Closure(T) $unmet
	 * @return T|void
	 */
	public function when( bool|Closure $condition, Closure $met, ?Closure $unmet = null ): static {
		if ( $condition instanceof Closure ) {
			$condition = (bool) call_user_func( $condition );
		}

		if ( $condition ) {
			$met( $this );
		} else {
			$unmet && $unmet( $this );
		}

		return $this;
	}

	public function render(): void {
		if ( $this->within_element ) {
			throw new \LogicException( 'Cannot render within an element' );
		}

		$this->formatter->print();
	}
}
