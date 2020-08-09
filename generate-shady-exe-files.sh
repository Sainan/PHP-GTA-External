#!/bin/bash

echo "get-module-base"
g++ -static -o cpp-bin/get-module-base.exe cpp-src/get-module-base.cpp

echo "get-module-path"
g++ -static -o cpp-bin/get-module-path.exe cpp-src/get-module-path.cpp

echo "read-bytes"
g++ -static -o cpp-bin/read-bytes.exe cpp-src/read-bytes.cpp

echo "write-bytes"
g++ -static -o cpp-bin/write-bytes.exe cpp-src/write-bytes.cpp
