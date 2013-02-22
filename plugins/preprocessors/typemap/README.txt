Using the PKP Metadata Harvester typemap plugin
===============================================

The typemap plugin converts the type element values from an archive to a set of 
other values. If a "map file" for each archive being harvested is defined, all
the archives can share a common set of "type" values in the user interface,
thereby allowing the user to select from a consistent set of values when
searching.

Each archive's map file is defined using the pattern

 typemap-[archive ID].xml

For example, if an archive's ID in the Harvester is 3, the typemap plugin
will look for a file in the plugin's directory named typemap-3.xml and
use the mappings defined in that file when the archive is harvested. 

You can determine each archive's ID by looking at the various URLs used in
the Manage Archives page in the Harvester Administration. The ID is the last
parameter in the URL that links to the Edit, Manage, and Delete tools.

The map files use the same internal format as the mapping.xml file included
in the languagemap plugin, which is simple XML containing one or more 'mapping'
elements with 'from' and 'to' attributes:

  <mapping from="Monograph" to="Book" />

All of the mappings are children of a single 'mappings' element, so that
a map file with two mappings would look like this:

  <mappings>
        <mapping from="Conference or Workshop Item" to="Presentation" />
        <mapping from="Monograph" to="Book" />
  </mappings>

The map file also contains an embedded DTD defining this format. See the
mapping.xml file included in the languagemap directory for more detail.
