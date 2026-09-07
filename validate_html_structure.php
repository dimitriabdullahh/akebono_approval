<?php

$file = __DIR__ . '/resources/views/portal/upload.blade.php';
$content = file_get_contents($file);

// Find the multi-part section
$startPos = strpos($content, '@if($request->hasMultipleParts())');
$endPos = strpos($content, '@endif', $startPos);

if ($startPos === false || $endPos === false) {
    echo "Cannot find multi-part section\n";
    exit(1);
}

$section = substr($content, $startPos, $endPos - $startPos + 6);

// Count opening and closing div tags
preg_match_all('/<div[^>]*>/', $section, $openDivs);
preg_match_all('/<\/div>/', $section, $closeDivs);

echo "=== HTML Structure Validation ===\n\n";
echo "Multi-part section analysis:\n";
echo "Opening <div> tags: " . count($openDivs[0]) . "\n";
echo "Closing </div> tags: " . count($closeDivs[0]) . "\n";
echo "Difference: " . (count($openDivs[0]) - count($closeDivs[0])) . "\n\n";

// Count @foreach and @endforeach
preg_match_all('/@foreach/', $section, $foreachs);
preg_match_all('/@endforeach/', $section, $endforeachs);

echo "Blade loop analysis:\n";
echo "@foreach: " . count($foreachs[0]) . "\n";
echo "@endforeach: " . count($endforeachs[0]) . "\n";
echo "Difference: " . (count($foreachs[0]) - count($endforeachs[0])) . "\n\n";

// Find the line with @endforeach that closes the parts loop
$lines = explode("\n", $section);
foreach ($lines as $num => $line) {
    if (strpos($line, '@endforeach') !== false) {
        echo "Line " . ($num + 1) . ": " . trim($line) . "\n";
    }
}

echo "\n=== Checking part loop structure ===\n";
$partLoopStart = strpos($section, '@foreach($request->requestParts as $partIndex => $part)');
$partLoopEnd = strrpos($section, '@endforeach');

if ($partLoopStart !== false && $partLoopEnd !== false) {
    $partLoop = substr($section, $partLoopStart, $partLoopEnd - $partLoopStart);
    
    // Count card openings and closings in part loop
    preg_match_all('/<div class="card mb-4 part-section/', $partLoop, $partCards);
    echo "Part section cards: " . count($partCards[0]) . " (should be inside loop, so rendered N times)\n";
    
    // Look for the closing div of card mb-4 part-section
    $lines = explode("\n", $partLoop);
    $cardDepth = 0;
    $inPartCard = false;
    
    foreach ($lines as $num => $line) {
        if (strpos($line, 'class="card mb-4 part-section') !== false) {
            echo "\nPart card opens at line " . ($num + 1) . "\n";
            $inPartCard = true;
            $cardDepth = 0;
        }
        
        if ($inPartCard) {
            $cardDepth += substr_count($line, '<div');
            $cardDepth -= substr_count($line, '</div>');
            
            if (strpos($line, '</div>') !== false && $cardDepth == 0) {
                echo "Part card closes at line " . ($num + 1) . ": " . trim($line) . "\n";
                $inPartCard = false;
            }
        }
    }
}

echo "\n=== End ===\n";
