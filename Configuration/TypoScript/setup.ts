# Module configuration
module.tx_deepltranslate {
  persistence {
    storagePid = module.tx_deepltranslate.persistence.storagePid
  }
  view {
    templateRootPaths.0 = module.tx_deepltranslate.view.templateRootPath
    partialRootPaths.0 = module.tx_deepltranslate.view.partialRootPath
    layoutRootPaths.0 = module.tx_deepltranslate.view.layoutRootPath
  }
}
