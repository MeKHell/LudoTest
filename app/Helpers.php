<?php

namespace App;


if(!function_exists('xmlToArray')){
    function xmlToArray(\SimpleXMLElement $xml): array
    {
        $parser = function (\SimpleXMLElement $xml, array $collection = []) use (&$parser) {
            $nodes = $xml->children();
            $attributes = $xml->attributes();

            if (0 !== count($attributes)) {
                foreach ($attributes as $attrName => $attrValue) {
                    $collection['xml_attr'][$attrName] = strval($attrValue);
                }
            }

            if (0 === $nodes->count()) {
                $collection['value'] = strval($xml);
                return $collection;
            }

            foreach ($nodes as $nodeName => $nodeValue) {
                if (count($nodeValue->xpath('../' . $nodeName)) < 2) {
                    $collection[$nodeName] = $parser($nodeValue);
                    continue;
                }

                $collection[$nodeName][] = $parser($nodeValue);
            }

            return $collection;
        };

        return [
            $xml->getName() => $parser($xml)
        ];
    }
}

if (!function_exists('bgg_query')) {
    function bgg_query(string $path, array $data)
    {
        if (!in_array($path, ['search', 'thing'])) {
            return null;
        }
        $api_key = config('app.src_list.bgg.api_key');
        $url = config('app.src_list.bgg.url') . '/' . $path;
        $req = curl_init();
        $data_url = http_build_query($data);
        curl_setopt_array($req, [
            CURLOPT_URL => $url . '?' . $data_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $api_key
            ]
        ]);
        $info = curl_getinfo($req);
        if (!$result = curl_exec($req)) {
            trigger_error(curl_error($req));
        }
        curl_close($req);

        $xml = simplexml_load_string($result);
        return xmlToArray($xml);
    }
}

if (!function_exists('extract_subarray')) {
    function extract_subarray(string $key, array &$arr): array | null{
        if (!is_array($arr) || !key_exists($key, $arr)) {
            return null;
        }
        $res = $arr[$key];
        unset($arr[$key]);
        return $res;
    }
}

if (!function_exists('lt_translate')) {
    function lt_translate(string $text, string $lang_code = 'en'): array {
        // TODO implement translation
        return ['language_code' => $lang_code, 'text' => $text];
    }
}

if (!function_exists('rm_blank_lines')) {
    function rm_blank_lines(string $text): string {
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
    }
}
