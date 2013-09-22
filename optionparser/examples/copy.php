<?php

require dirname(__FILE__) . '/../lib/OptionParser.php';

$debug = false;
function enable_debugging()
{
    global $debug;
    $debug = true;
}

$parser = new OptionParser;
$parser->addHead("Copies the contents of one file (or stream) to another.\n");
$parser->addHead("Usage: " . basename($argv[0]) . " -i <input> [ options ]\n");
$parser->addRule('d|debug', 'enable_debugging', "Enable debug mode");
$parser->addRule('i|input::', 'The input file to read, use "-" for stdin');
$parser->addRule('o|output:', '(optional) The output file to write to, defaults to stdout');
$parser->addRule('help', 'Display a help message and exit');
$parser->addTail("\nThis program brought to you by me!\n");

try {
    $parser->parse();
} catch (Exception $e) {
    die($parser->getUsage());
}

if ($parser->help) {
    die($parser->getUsage());
}
if (!isset($parser->input)) {
    die("You must specify an input file!\n");
}

if ($parser->input == '-') {
    $input = 'php://stdin';
} else {
    $input = $parser->input;
}

$in = @fopen($input, 'rb');
if ($in === false) {
    die("Unable to open input stream: $input\n");
}

if (isset($parser->output)) {
    if ($parser->output === true) {
        $output = 'php://stdout';
    } else {
        $output = $parser->output;
    }
} else {
    $output = 'php://stdout';
}

$out = @fopen($output, 'w');
if ($out === false) {
    die("Unable to open output stream: $output\n");
}

while (($data = fread($in, 512)) !== '') {
    fwrite($out, $data);
}

fclose($in);
fclose($out);

if ($debug) {
    // let the user know we're done
    echo "Finished!\n";
}

