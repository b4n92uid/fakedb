**FakeDB** is a php cli tool to generate random fake data into a database (MySQL ?),
it use an xml descriptor file to generate data according to a certain pattern or format,
it has many interesting features like download file by url, filter data, range generation...etc.

# Usage

`php fakedb.php [-c <number>] [-d <key=value>] [-p] xmlfile`

`xmlfile`
    Required. XML File path

`-c/--count <argument>`
    Generation iteration count

`-d/--define <argument>`
    A define to override file value (key1=value1;key2=value2)

`-p/--print-only`
    Only print result

# Usage example

Generate 10 product<br>
`php fakedb.php product.xml -c 10`

Preview the generation of 10 product in the console<br>
`php fakedb.php product.xml -c 10 -p`

Define a variable to use in descriptor file<br>
`php fakedb.php product.xml -d key=value`

# File descriptor (xml file)

## Example of a descriptor xml file

``` xml
<table host="localhost" user="root" pass="" dbname="database" name="product" >
    <data column="name" pattern="[w:1,3]" />
    <data column="date" pattern="[d:2010-01-01,2012-06-01]" />
    <data column="price" pattern="[n:800,170000,-2]" />
    <data column="description" pattern="[t:64,128]" />
    <data column="img" pattern="[url:$src,$dst,jpg|basename]"
        var-src="http://lorempixel.com/640/480/technics/"
        var-dst="D:\project\website.com\uploads\product" />
    <data column="slug" pattern="[f:name|slug]" />
    <data column="hits" pattern="[n:0,512]" />
    <data column="discount" pattern="[s:0,0,2000,501]" />
    <data column="store_id" pattern="[b:store.id]" />
</table>
```

## Tag reference

`[e:string1, string2, ...]`
Just echo given string with space

`[w:min,max]`
Generate a word with with random length
between min and max

`[t:min,max]`
Generate a text with with random words
between min and max

`[n:min,max]`
Generate a number
between min and max

`[w:min,max,numeric]`
Generate a sequence of chararcters
between min and max length and optionally include number

`[q:min,max]`
Generate a sequence of number only
between min and max length

`[a:item1,item2,...]`
Return a serialized array with given items

`[f:column]`
Return the value of the column of the current record
must be already generated

`[b:table.column]`
Return the value of the column from table internaly make a sql query to fetch value
must be already recorded

`[s:item1,item2,...]`
Pick a random item from the given list

`[p:path]`
Pick a random filename from specified path

`[d:start,end]`
Return a random date between start and end

`[m:host.tld]`
Generate a mail with optionally with a specified host and tld

`[url:src,dst,ext]`
Fetch a file from src param, save it in dst dirpath with a generated filename and optionally the specified extention

`[web]`
Generate a url adress of a website with optionally specified tld
