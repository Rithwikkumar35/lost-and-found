<?php

function generateVerificationQuestions(
    $title,
    $description,
    $category
){

    $title_lower =
    strtolower($title . ' ' . $description . ' ' . $category);

    $questions = array();

    // Mobile / Phone
    if (
        strpos($title_lower, 'mobile') !== false ||
        strpos($title_lower, 'phone') !== false ||
        strpos($title_lower, 'iphone') !== false ||
        strpos($title_lower, 'android') !== false
    ) {

        $questions[] = "What is the brand of the phone?";
        $questions[] = "What is the model of the phone?";
        $questions[] = "What wallpaper or lock screen image is set?";
        $questions[] = "Any unique scratch, sticker, or mark?";
    }

    // Wallet
    elseif (
        strpos($title_lower, 'wallet') !== false
    ) {

        $questions[] = "What is the color of the wallet?";
        $questions[] = "Which cards are inside the wallet?";
        $questions[] = "How much cash was inside?";
        $questions[] = "Any unique mark or sticker?";
    }

    // Laptop
    elseif (
        strpos($title_lower, 'laptop') !== false ||
        strpos($title_lower, 'macbook') !== false
    ) {

        $questions[] = "What is the laptop brand?";
        $questions[] = "What is the laptop color?";
        $questions[] = "What wallpaper is set on the screen?";
        $questions[] = "Any stickers or scratches?";
    }

    // Bag / Backpack
    elseif (
        strpos($title_lower, 'bag') !== false ||
        strpos($title_lower, 'backpack') !== false
    ) {

        $questions[] = "What is the brand of the bag?";
        $questions[] = "What color is the bag?";
        $questions[] = "What items are inside the bag?";
        $questions[] = "Any sticker or keychain attached?";
    }

    // ID Card
    elseif (
        strpos($title_lower, 'id') !== false ||
        strpos($title_lower, 'card') !== false
    ) {

        $questions[] = "Which institution issued the ID card?";
        $questions[] = "What is the holder's name?";
        $questions[] = "What department or course is mentioned?";
        $questions[] = "What are the last 4 digits of the ID number?";
    }

    // Keys
    elseif (
        strpos($title_lower, 'key') !== false ||
        strpos($title_lower, 'keys') !== false
    ) {

        $questions[] = "How many keys are attached?";
        $questions[] = "Is there a keychain attached?";
        $questions[] = "What color is the keychain?";
        $questions[] = "Any unique identifier or tag?";
    }

    // Watch
    elseif (
        strpos($title_lower, 'watch') !== false
    ) {

        $questions[] = "What is the watch brand?";
        $questions[] = "What color is the strap?";
        $questions[] = "Is it analog or digital?";
        $questions[] = "Any scratches or unique marks?";
    }

    // Default questions
    else {

        $questions[] = "What is the brand of the item?";
        $questions[] = "What is the color of the item?";
        $questions[] = "What unique marks or stickers does it have?";
        $questions[] = "What detail would only the real owner know?";
    }

    // Ensure exactly 4 questions
    while (count($questions) < 4) {
        $questions[] = "Provide an additional identifying detail.";
    }

    return array_slice($questions, 0, 4);
}
?>