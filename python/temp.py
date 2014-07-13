# coding=UTF-8  
# file: example1.py  
import string  
  
# 这个是 str 的字符串  
s = '关关雎鸠'  
  
# 这个是 unicode 的字符串  
u = u'关关雎鸠'  
  
print isinstance(s, str)  # True  
print isinstance(u, unicode)  # True  
  
print s.__class__  # <type 'str'>  
print u.__class__  # <type 'unicode'>
print s


#!/usr/bin/python
# import json
# import urllib
# import urllib2
# url = "http://outofmemory.cn/code-snippet/83/sanzhong-Python-xiazai-url-save-file-code"
# urllib.urlretrieve(url, "./1.html")
