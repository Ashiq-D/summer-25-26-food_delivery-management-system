<?php

function cleanInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);

    return $data;
}


/**
 * Resolves the web-facing image path for a Food/Restaurant item, given a
 * (possibly empty, possibly stale) DB path and a ready-made fallback path.
 *
 * Guards against more than just an empty column: if $dbPath is set but the
 * file it points to no longer exists on disk (deleted upload, bad manual
 * insert, stale seed data, etc.), this still falls back to $fallbackWebPath
 * instead of returning a broken image link.
 *
 * $dbPath           - the raw value from Image_Path/Profile_Image_Path
 *                      (relative to the project root, e.g.
 *                      "assets/uploads/food/food_3_....jpg"), or null/empty
 * $fallbackWebPath  - the web-relative fallback path to use when $dbPath is
 *                      empty or missing on disk (e.g.
 *                      "../../assets/images/food3.jpg")
 * $webPrefix        - prefix to prepend to $dbPath to make it web-relative,
 *                      matching $fallbackWebPath's base (default "../../")
 *
 * Returns a web-relative path, always safe to put straight into an <img src>.
 */
function resolveImagePath($dbPath, $fallbackWebPath, $webPrefix = "../../")
{
    if (empty($dbPath))
    {
        return $fallbackWebPath;
    }

    $onDisk = __DIR__ . "/../" . $dbPath;

    if (!file_exists($onDisk))
    {
        return $fallbackWebPath;
    }

    return $webPrefix . $dbPath;
}


/**
 * Handles a profile-picture upload from an <input type="file"> field.
 *
 * $fileField    - the key in $_FILES to read (e.g. "profile_picture")
 * $subfolder    - subfolder under assets/uploads/ to save into (e.g. "admin")
 * $prefix       - filename prefix, usually the user's ID (e.g. "admin_3")
 *
 * Returns:
 *   - a path string (relative to the project root, e.g. "assets/uploads/admin/admin_3_....jpg")
 *     on success
 *   - null if the field was empty (no file chosen - not an error)
 *   - false if a file was chosen but it failed validation/upload
 */
function handleProfileImageUpload($fileField, $subfolder, $prefix)
{
    if (!isset($_FILES[$fileField]) || $_FILES[$fileField]["error"] === UPLOAD_ERR_NO_FILE)
    {
        return null;
    }

    if ($_FILES[$fileField]["error"] !== UPLOAD_ERR_OK)
    {
        return false;
    }

    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
    ];

    $maxBytes = 2 * 1024 * 1024; // 2MB

    if ($_FILES[$fileField]["size"] > $maxBytes)
    {
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES[$fileField]["tmp_name"]);
    finfo_close($finfo);

    if (!isset($allowedTypes[$mimeType]))
    {
        return false;
    }

    $extension = $allowedTypes[$mimeType];

    $uploadDir = __DIR__ . "/../assets/uploads/" . $subfolder . "/";

    if (!is_dir($uploadDir))
    {
        mkdir($uploadDir, 0755, true);
    }

    $filename = $prefix . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $extension;

    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES[$fileField]["tmp_name"], $destination))
    {
        return false;
    }

    return "assets/uploads/" . $subfolder . "/" . $filename;
}
