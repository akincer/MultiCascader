# MultiCascader
Symfony class to manually handle cascade operations for Doctrine ODM to cover unidirectional cascades.

# Use

Place in a folder in src. It's configured currently for a Utils folder off src, but you can customize this just change the namespace. Autowire it directly into your function that handles delete requests.

```
public function deleteMyDocment(DocumentManager $dm, Request $request, MultiCascader $cascader)
{
  // Get MyDocument from post
  $myDocument_id = $request->get('id');

  // Get matching document
  $repository = $dm->getRepository(MyDocument::class);
  $myDocument = $repository->findOneBy(['id' => $myDocument_id]);

  // Detach associations
  $cascader->detachAssociations($myDocument);

  // Delete from database
  $dm->remove($myDocument);
  $dm->flush();
}
```

# Notes

This only has limited testing with storAs=id only and rudimentary error checking. It may very well work for all forms of storeAs. The overhead associated with it is unknown ATM. There may be more elegant ways to accomplish this but I couldn't find it. It's a blunt force instrument ATM -- it doesn't care whether an association is unidirectional or bidirectional and would interrupt any orphan removal preferences as-is I think.

# Remove functions

Remove functions in classes should simply remove the reference or embedded document.

Example:

```
public function removeMyDocument(MyDocument $myDocument)
{
    // Remove the document from the myDocuments collection
    $this->myDocuments->removeElement($myDocument);
}
```
