module.tx_deepltranslate {
  view {
    # cat=module.tx_relatedproductstool/file; type=string; label=Path to template root (BE)
    templateRootPath = EXT:deepltranslate/Resources/Private/Templates/
    # cat=module.tx_relatedproductstool/file; type=string; label=Path to template partials (BE)
    partialRootPath = EXT:deepltranslate/Resources/Private/Partials/
    # cat=module.tx_relatedproductstool/file; type=string; label=Path to template layouts (BE)
    layoutRootPath = EXT:deepltranslate/Resources/Private/Layouts/
  }
  persistence {
    # cat=module.tx_relatedproductstool//a; type=string; label=Default storage PID
    storagePid =
  }
}
