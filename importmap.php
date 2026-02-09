<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'admin' => [
        'path' => './assets/admin.js',
        'entrypoint' => true,
    ],
    'wiki' => [
        'path' => './assets/wiki.js',
        'entrypoint' => true,
    ],
    'app.css' => [
        'path' => 'app.built.css',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@symfony/ux-autocomplete' => [
        'path' => './vendor/symfony/ux-autocomplete/assets/dist/controller.js',
    ],
    'tom-select' => [
        'version' => '2.4.3',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    'fos-router' => [
        'version' => '2.4.6',
    ],
    'moment' => [
        'version' => '2.30.1',
    ],
    'tom-select/dist/css/tom-select.bootstrap4.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'scheduler' => [
        'version' => '0.23.2',
    ],
    '@tiptap/core' => [
        'version' => '3.18.0',
    ],
    '@tiptap/starter-kit' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-link' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-image' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-text-align' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-code-block-lowlight' => [
        'version' => '3.17.1',
    ],
    'lowlight' => [
        'version' => '3.3.0',
    ],
    '@tiptap/pm/transform' => [
        'version' => '3.18.0',
    ],
    '@tiptap/pm/commands' => [
        'version' => '3.18.0',
    ],
    '@tiptap/pm/state' => [
        'version' => '3.18.0',
    ],
    '@tiptap/pm/model' => [
        'version' => '3.18.0',
    ],
    '@tiptap/pm/schema-list' => [
        'version' => '3.18.0',
    ],
    '@tiptap/pm/view' => [
        'version' => '3.18.0',
    ],
    '@tiptap/pm/keymap' => [
        'version' => '3.18.0',
    ],
    '@tiptap/extension-blockquote' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-bold' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-code' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-code-block' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-document' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-hard-break' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-heading' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-horizontal-rule' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-italic' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-list' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-paragraph' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-strike' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-text' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extension-underline' => [
        'version' => '3.17.1',
    ],
    '@tiptap/extensions' => [
        'version' => '3.17.1',
    ],
    'linkifyjs' => [
        'version' => '4.3.2',
    ],
    'highlight.js/lib/core' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/1c' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/abnf' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/accesslog' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/actionscript' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/ada' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/angelscript' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/apache' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/applescript' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/arcade' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/armasm' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/asciidoc' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/aspectj' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/autohotkey' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/autoit' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/avrasm' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/awk' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/axapta' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/basic' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/bnf' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/brainfuck' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/cal' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/capnproto' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/ceylon' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/clean' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/clojure' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/clojure-repl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/cmake' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/coffeescript' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/coq' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/cos' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/crmsh' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/crystal' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/csp' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/d' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/dart' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/delphi' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/django' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/dns' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/dockerfile' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/dos' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/dsconfig' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/dts' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/dust' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/ebnf' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/elixir' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/elm' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/erb' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/erlang' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/erlang-repl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/excel' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/fix' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/flix' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/fortran' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/fsharp' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/gams' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/gauss' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/gcode' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/gherkin' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/glsl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/gml' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/golo' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/gradle' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/groovy' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/haml' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/handlebars' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/haskell' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/haxe' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/hsp' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/http' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/hy' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/inform7' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/irpf90' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/isbl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/jboss-cli' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/julia' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/julia-repl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/lasso' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/latex' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/ldif' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/leaf' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/lisp' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/livecodeserver' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/livescript' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/llvm' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/lsl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/mathematica' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/matlab' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/maxima' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/mel' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/mercury' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/mipsasm' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/mizar' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/mojolicious' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/monkey' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/moonscript' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/n1ql' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/nestedtext' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/nginx' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/nim' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/nix' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/node-repl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/nsis' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/ocaml' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/openscad' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/oxygene' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/parser3' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/pf' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/pgsql' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/pony' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/powershell' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/processing' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/profile' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/prolog' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/properties' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/protobuf' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/puppet' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/purebasic' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/q' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/qml' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/reasonml' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/rib' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/roboconf' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/routeros' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/rsl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/ruleslanguage' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/sas' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/scala' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/scheme' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/scilab' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/smali' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/smalltalk' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/sml' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/sqf' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/stan' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/stata' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/step21' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/stylus' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/subunit' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/taggerscript' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/tap' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/tcl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/thrift' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/tp' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/twig' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/vala' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/vbscript' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/vbscript-html' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/verilog' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/vhdl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/vim' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/wren' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/x86asm' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/xl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/xquery' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/zephir' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/arduino' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/bash' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/c' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/cpp' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/csharp' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/css' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/diff' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/go' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/graphql' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/ini' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/java' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/javascript' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/json' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/kotlin' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/less' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/lua' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/makefile' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/markdown' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/objectivec' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/perl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/php' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/php-template' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/plaintext' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/python' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/python-repl' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/r' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/ruby' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/rust' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/scss' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/shell' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/sql' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/swift' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/typescript' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/vbnet' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/wasm' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/xml' => [
        'version' => '11.11.0',
    ],
    'highlight.js/lib/languages/yaml' => [
        'version' => '11.11.0',
    ],
    'devlop' => [
        'version' => '1.1.0',
    ],
    'prosemirror-transform' => [
        'version' => '1.11.0',
    ],
    'prosemirror-commands' => [
        'version' => '1.7.1',
    ],
    'prosemirror-state' => [
        'version' => '1.4.4',
    ],
    'prosemirror-model' => [
        'version' => '1.25.4',
    ],
    'prosemirror-schema-list' => [
        'version' => '1.5.1',
    ],
    'prosemirror-view' => [
        'version' => '1.41.5',
    ],
    'prosemirror-keymap' => [
        'version' => '1.2.3',
    ],
    '@tiptap/core/jsx-runtime' => [
        'version' => '3.17.1',
    ],
    '@tiptap/pm/dropcursor' => [
        'version' => '3.17.1',
    ],
    '@tiptap/pm/gapcursor' => [
        'version' => '3.17.1',
    ],
    '@tiptap/pm/history' => [
        'version' => '3.17.1',
    ],
    'orderedmap' => [
        'version' => '2.1.1',
    ],
    'w3c-keyname' => [
        'version' => '2.2.8',
    ],
    'prosemirror-dropcursor' => [
        'version' => '1.8.2',
    ],
    'prosemirror-gapcursor' => [
        'version' => '1.4.0',
    ],
    'prosemirror-history' => [
        'version' => '1.5.0',
    ],
    'prosemirror-view/style/prosemirror.min.css' => [
        'version' => '1.41.5',
        'type' => 'css',
    ],
    'rope-sequence' => [
        'version' => '1.3.4',
    ],
    'prosemirror-gapcursor/style/gapcursor.min.css' => [
        'version' => '1.4.0',
        'type' => 'css',
    ],
    '@tiptap/extension-youtube' => [
        'version' => '3.18.0',
    ],
    '@tiptap/extension-highlight' => [
        'version' => '3.18.0',
    ],
    '@tiptap/extension-table' => [
        'version' => '3.18.0',
    ],
    '@tiptap/pm/tables' => [
        'version' => '3.18.0',
    ],
    'prosemirror-tables' => [
        'version' => '1.8.5',
    ],
    'prosemirror-tables/style/tables.min.css' => [
        'version' => '1.8.5',
        'type' => 'css',
    ],
    '@tiptap/extension-table-row' => [
        'version' => '3.18.0',
    ],
    '@tiptap/extension-table-header' => [
        'version' => '3.18.0',
    ],
    '@tiptap/extension-table-cell' => [
        'version' => '3.18.0',
    ],
    '@tiptap/extension-text-style' => [
        'version' => '3.18.0',
    ],
    '@tiptap/extension-color' => [
        'version' => '3.18.0',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'sortablejs' => [
        'version' => '1.15.6',
    ],
];
