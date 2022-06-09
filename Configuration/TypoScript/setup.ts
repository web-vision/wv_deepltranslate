# Module configuration
module.tx_wvdeepltranslate {
  persistence {
    storagePid = module.tx_wvdeepltranslate.persistence.storagePid
  }
  view {
    templateRootPaths.0 = module.tx_wvdeepltranslate.view.templateRootPath
    partialRootPaths.0 = module.tx_wvdeepltranslate.view.partialRootPath
    layoutRootPaths.0 = module.tx_wvdeepltranslate.view.layoutRootPath
  }

  settings {
    glossaries {
      # Add glossaries here in the format <source_language>-<target_language> = <glossary_guid>
      # en-de = bc068b6e-1d91-4b8a-a7f9-eb6cb4affe78
    }
  }
}