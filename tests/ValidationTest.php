<?php

namespace Tests;

use Astrotomic\Translatable\Locales;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;

final class ValidationTest extends TestCase
{
    #[Test]
    public function it_does_not_touch_untranslated_keys(): void
    {
        $rules = [
            'title' => 'required',
            'author_id' => [
                'required',
                'int',
            ],
        ];

        self::assertEquals($rules, RuleFactory::make($rules));
    }

    #[Test]
    public function format_array_it_replaces_single_key(): void
    {
        $rules = [
            'title' => 'required',
            '%content%' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'en.content' => 'required',
            'de.content' => 'required',
            'de-DE.content' => 'required',
            'de-AT.content' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY));
    }

    #[Test]
    public function format_array_it_replaces_sub_key(): void
    {
        $rules = [
            'title' => 'required',
            'translations.%content%' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'translations.en.content' => 'required',
            'translations.de.content' => 'required',
            'translations.de-DE.content' => 'required',
            'translations.de-AT.content' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY));
    }

    #[Test]
    public function format_array_it_replaces_middle_key(): void
    {
        $rules = [
            'title' => 'required',
            'translations.%content%.body' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
            'translations.de-DE.content.body' => 'required',
            'translations.de-AT.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY));
    }

    #[Test]
    public function format_array_it_replaces_middle_key_with_custom_prefix(): void
    {
        $rules = [
            'title' => 'required',
            'translations.{content%.body' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
            'translations.de-DE.content.body' => 'required',
            'translations.de-AT.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '{'));
    }

    #[Test]
    public function format_array_it_replaces_middle_key_with_custom_suffix(): void
    {
        $rules = [
            'title' => 'required',
            'translations.%content}.body' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
            'translations.de-DE.content.body' => 'required',
            'translations.de-AT.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '%', '}'));
    }

    #[Test]
    public function format_array_it_replaces_middle_key_with_custom_delimiters(): void
    {
        $rules = [
            'title' => 'required',
            'translations.{content}.body' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
            'translations.de-DE.content.body' => 'required',
            'translations.de-AT.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '{', '}'));
    }

    #[Test]
    public function format_array_it_replaces_middle_key_with_custom_regex_delimiters(): void
    {
        $rules = [
            'title' => 'required',
            'translations.$content$.body' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
            'translations.de-DE.content.body' => 'required',
            'translations.de-AT.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '$', '$'));
    }

    #[Test]
    public function format_array_it_uses_config_as_default(): void
    {
        app('config')->set('translatable.rule_factory', [
            'format' => RuleFactory::FORMAT_ARRAY,
            'prefix' => '{',
            'suffix' => '}',
        ]);

        $rules = [
            'title' => 'required',
            '{content}' => 'required',
            '%content%' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            '%content%' => 'required',
            'en.content' => 'required',
            'de.content' => 'required',
            'de-DE.content' => 'required',
            'de-AT.content' => 'required',
        ], RuleFactory::make($rules));
    }

    #[Test]
    public function format_key_it_replaces_single_key(): void
    {
        $rules = [
            'title' => 'required',
            '%content%' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'content:en' => 'required',
            'content:de' => 'required',
            'content:de-DE' => 'required',
            'content:de-AT' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_KEY));
    }

    #[Test]
    public function format_key_it_replaces_sub_key(): void
    {
        $rules = [
            'title' => 'required',
            'translations.%content%' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'translations.content:en' => 'required',
            'translations.content:de' => 'required',
            'translations.content:de-DE' => 'required',
            'translations.content:de-AT' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_KEY));
    }

    #[Test]
    public function format_key_it_replaces_middle_key(): void
    {
        $rules = [
            'title' => 'required',
            'translations.%content%.body' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'translations.content:en.body' => 'required',
            'translations.content:de.body' => 'required',
            'translations.content:de-DE.body' => 'required',
            'translations.content:de-AT.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_KEY));
    }

    #[Test]
    public function format_key_it_uses_config_as_default(): void
    {
        app('config')->set('translatable.rule_factory', [
            'format' => RuleFactory::FORMAT_KEY,
            'prefix' => '{',
            'suffix' => '}',
        ]);

        $rules = [
            'title' => 'required',
            '{content}' => 'required',
            '%content%' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            '%content%' => 'required',
            'content:en' => 'required',
            'content:de' => 'required',
            'content:de-DE' => 'required',
            'content:de-AT' => 'required',
        ], RuleFactory::make($rules));
    }

    #[Test]
    public function it_replaces_key_with_custom_locales(): void
    {
        $rules = [
            'title' => 'required',
            'translations.%content%.body' => 'required',
        ];

        self::assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '%', '%', [
            'en',
            'de',
        ]));
    }

    #[Test]
    public function it_throws_exception_with_undefined_locales(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $rules = [
            'title' => 'required',
            'translations.$content$.body' => 'required',
        ];

        RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '%', '%', [
            'en',
            'de',
            'at',
        ]);
    }

    #[Test]
    public function format_array_it_replaces_single_rule(): void
    {
        $rules = [
            '%title%' => 'sometimes|string',
            '%content%' => 'required_with:%title%',
        ];

        self::assertEquals([
            'en.title' => 'sometimes|string',
            'de.title' => 'sometimes|string',
            'de-DE.title' => 'sometimes|string',
            'de-AT.title' => 'sometimes|string',

            'en.content' => 'required_with:en.title',
            'de.content' => 'required_with:de.title',
            'de-DE.content' => 'required_with:de-DE.title',
            'de-AT.content' => 'required_with:de-AT.title',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY));
    }

    #[Test]
    public function format_array_it_replaces_imploded_rules(): void
    {
        $rules = [
            '%title%' => 'sometimes|string',
            '%content%' => 'required_with:%title%|string',
        ];

        self::assertEquals([
            'en.title' => 'sometimes|string',
            'de.title' => 'sometimes|string',
            'de-DE.title' => 'sometimes|string',
            'de-AT.title' => 'sometimes|string',

            'en.content' => 'required_with:en.title|string',
            'de.content' => 'required_with:de.title|string',
            'de-DE.content' => 'required_with:de-DE.title|string',
            'de-AT.content' => 'required_with:de-AT.title|string',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY));
    }

    #[Test]
    public function format_array_it_replaces_array_of_rules(): void
    {
        $rules = [
            '%title%' => 'sometimes|string',
            '%content%' => ['required_with:%title%', 'string'],
        ];

        self::assertEquals([
            'en.title' => 'sometimes|string',
            'de.title' => 'sometimes|string',
            'de-DE.title' => 'sometimes|string',
            'de-AT.title' => 'sometimes|string',

            'en.content' => ['required_with:en.title', 'string'],
            'de.content' => ['required_with:de.title', 'string'],
            'de-DE.content' => ['required_with:de-DE.title', 'string'],
            'de-AT.content' => ['required_with:de-AT.title', 'string'],
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY));
    }

    #[Test]
    public function format_array_it_does_not_touch_non_string_rule(): void
    {
        $rules = [
            'title' => 'required',
            '%content%' => Rule::requiredIf(function () {
                return true;
            }),
        ];

        $formattedRules = RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY);

        self::assertEquals('required', $formattedRules['title']);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['en.content']);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['de.content']);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['de-DE.content']);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['de-AT.content']);
    }

    #[Test]
    public function format_array_it_does_not_touch_non_string_rule_in_array(): void
    {
        $rules = [
            'title' => 'required',
            '%content%' => [
                'required_with:%title%',
                Rule::requiredIf(function () {
                    return true;
                }),
            ],
        ];

        $formattedRules = RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY);

        self::assertEquals('required', $formattedRules['title']);
        self::assertEquals('required_with:en.title', $formattedRules['en.content'][0]);
        self::assertEquals('required_with:de.title', $formattedRules['de.content'][0]);
        self::assertEquals('required_with:de-DE.title', $formattedRules['de-DE.content'][0]);
        self::assertEquals('required_with:de-AT.title', $formattedRules['de-AT.content'][0]);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['en.content'][1]);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['de.content'][1]);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['de-DE.content'][1]);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['de-AT.content'][1]);
    }

    #[Test]
    public function format_key_it_replaces_single_rule(): void
    {
        $rules = [
            '%title%' => 'sometimes|string',
            '%content%' => 'required_with:"%title%"',
        ];

        self::assertEquals([
            'title:en' => 'sometimes|string',
            'title:de' => 'sometimes|string',
            'title:de-DE' => 'sometimes|string',
            'title:de-AT' => 'sometimes|string',

            'content:en' => 'required_with:"title:en"',
            'content:de' => 'required_with:"title:de"',
            'content:de-DE' => 'required_with:"title:de-DE"',
            'content:de-AT' => 'required_with:"title:de-AT"',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_KEY));
    }

    #[Test]
    public function format_key_it_replaces_imploded_rules(): void
    {
        $rules = [
            '%title%' => 'sometimes|string',
            '%content%' => 'required_with:"%title%"|string',
        ];

        self::assertEquals([
            'title:en' => 'sometimes|string',
            'title:de' => 'sometimes|string',
            'title:de-DE' => 'sometimes|string',
            'title:de-AT' => 'sometimes|string',

            'content:en' => 'required_with:"title:en"|string',
            'content:de' => 'required_with:"title:de"|string',
            'content:de-DE' => 'required_with:"title:de-DE"|string',
            'content:de-AT' => 'required_with:"title:de-AT"|string',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_KEY));
    }

    #[Test]
    public function format_key_it_replaces_array_of_rules(): void
    {
        $rules = [
            '%title%' => 'sometimes|string',
            '%content%' => ['required_with:"%title%"', 'string'],
        ];

        self::assertEquals([
            'title:en' => 'sometimes|string',
            'title:de' => 'sometimes|string',
            'title:de-DE' => 'sometimes|string',
            'title:de-AT' => 'sometimes|string',

            'content:en' => ['required_with:"title:en"', 'string'],
            'content:de' => ['required_with:"title:de"', 'string'],
            'content:de-DE' => ['required_with:"title:de-DE"', 'string'],
            'content:de-AT' => ['required_with:"title:de-AT"', 'string'],
        ], RuleFactory::make($rules, RuleFactory::FORMAT_KEY));
    }

    #[Test]
    public function format_key_it_does_not_touch_non_string_rule(): void
    {
        $rules = [
            'title' => 'required',
            '%content%' => Rule::requiredIf(function () {
                return true;
            }),
        ];

        $formattedRules = RuleFactory::make($rules, RuleFactory::FORMAT_KEY);

        self::assertEquals('required', $formattedRules['title']);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['content:en']);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['content:de']);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['content:de-DE']);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['content:de-AT']);
    }

    #[Test]
    public function format_key_it_does_not_touch_non_string_rule_in_array(): void
    {
        $rules = [
            'title' => 'required',
            '%content%' => [
                'required_with:"%title%"',
                Rule::requiredIf(function () {
                    return true;
                }),
            ],
        ];

        $formattedRules = RuleFactory::make($rules, RuleFactory::FORMAT_KEY);

        self::assertEquals('required', $formattedRules['title']);
        self::assertEquals('required_with:"title:en"', $formattedRules['content:en'][0]);
        self::assertEquals('required_with:"title:de"', $formattedRules['content:de'][0]);
        self::assertEquals('required_with:"title:de-DE"', $formattedRules['content:de-DE'][0]);
        self::assertEquals('required_with:"title:de-AT"', $formattedRules['content:de-AT'][0]);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['content:en'][1]);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['content:de'][1]);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['content:de-DE'][1]);
        self::assertInstanceOf(RequiredIf::class, $formattedRules['content:de-AT'][1]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        app('config')->set('translatable.locales', [
            'en',
            'de' => [
                'DE',
                'AT',
            ],
        ]);

        app(Locales::class)->load();
    }
}
