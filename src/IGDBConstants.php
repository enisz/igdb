<?php

    define("IGDBW_ENDPOINTS", array(
        "age_rating" => "age_ratings",
        "age_rating_content_description" => "age_rating_content_descriptions",
        "alternative_name" => "alternative_names",
        "artwork" => "artworks",
        "character" => "characters",
        "character_mug_shot" => "character_mug_shots",
        "collection" => "collections",
        "collection_membership" => "collection_memberships",
        "collection_membership_type" => "collection_membership_types",
        "collection_relation" => "collection_relations",
        "collection_relation_type" => "collection_relation_types",
        "collection_type" => "collection_types",
        "company" => "companies",
        "company_logo" => "company_logos",
        "company_website" => "company_websites",
        "cover" => "covers",
        "event" => "events",
        "event_logo" => "event_logos",
        "event_network" => "event_networks",
        "external_game" => "external_games",
        "franchise" => "franchises",
        "game" => "games",
        "game_engine" => "game_engines",
        "game_engine_logo" => "game_engine_logos",
        "game_localization" => "game_localizations",
        "game_mode" => "game_modes",
        "game_version" => "game_versions",
        "game_version_feature" => "game_version_features",
        "game_version_feature_value" => "game_version_feature_values",
        "game_video" => "game_videos",
        "genre" => "genres",
        "involved_company" => "involved_companies",
        "keyword" => "keywords",
        "language_support" => "language_supports",
        "language_support_type" => "language_support_types",
        "language" => "languages",
        "multiplayer_mode" => "multiplayer_modes",
        "multiquery" => "multiquery",
        "network_type" => "network_types",
        "platform" => "platforms",
        "platform_family" => "platform_families",
        "platform_logo" => "platform_logos",
        "platform_version" => "platform_versions",
        "platform_version_company" => "platform_version_companies",
        "platform_version_release_date" => "platform_version_release_dates",
        "platform_website" => "platform_websites",
        "player_perspective" => "player_perspectives",
        "region" => "regions",
        "release_date" => "release_dates",
        "release_date_status" => "release_date_statuses",
        "screenshot" => "screenshots",
        "search" => "search",
        "theme" => "themes",
        "website" => "websites"
    ));

    define("IGDBW_IMAGE_SIZES", array(
        "cover_small",
        "cover_small_2x",
        "screenshot_med",
        "screenshot_med_2x",
        "cover_big",
        "cover_big_2x",
        "logo_med",
        "logo_med_2x",
        "screenshot_big",
        "screenshot_big_2x",
        "screenshot_huge",
        "screenshot_huge_2x",
        "thumb",
        "thumb_2x",
        "micro",
        "micro_2x",
        "720p",
        "720p_2x",
        "1080p",
        "1080p_2x"
    ));

    define("IGDBW_WEBHOOK_ACTIONS", array(
        "create",
        "update",
        "delete"
    ));

    define('IGDBW_POSTFIXES', array(
        "=",
        "!=",
        ">",
        ">=",
        "<",
        "<=",
        "~"
    ));

    define("IGDBW_API_URL", "https://api.igdb.com/v4");

    define("IGDBW_BUILDER_DEFAULTS", array(
        "limit" => 10,
        "offset" => 0
    ));

?>
