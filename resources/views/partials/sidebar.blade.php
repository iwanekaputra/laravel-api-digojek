<div id="left-sidebar" class="sidebar">

    <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-arrow-left"></i></button>
    <div class="sidebar-scroll">
        <div class="user-account">
            <img src="/images/logo.png" class="rounded-circle user-photo" alt="User Profile Picture">
            <div class="dropdown">
                <span>Welcome,</span>
                <a href="javascript:void(0);" class="dropdown-toggle user-name"
                    data-toggle="dropdown"><strong>{{ auth()->user()->name }}</strong></a>
                <p class=" user-name"><strong>
                        {{ auth()->user()->code_referal == null ? '' : 'Referal : ' . auth()->user()->code_referal }}</strong>
                </p>
                <ul class="dropdown-menu dropdown-menu-right account">
                    <li><a href="page-profile2.html"><i class="icon-user"></i>My Profile</a></li>
                    <li><a href="app-inbox.html"><i class="icon-envelope-open"></i>Messages</a></li>
                    <li><a href="javascript:void(0);"><i class="icon-settings"></i>Settings</a></li>
                    <li class="divider"></li>
                    <li><a href="page-login.html"><i class="icon-power"></i>Logout</a></li>
                </ul>
            </div>
            <hr>
            <ul class="row list-unstyled">
                <li class="col-4">
                    <small>Total Users</small>
                    <h6>{{ DB::table('customers')->get()->count() }}</h6>
                </li>
                <li class="col-4">
                    <small>Order</small>
                    <h6>0</h6>
                </li>
                <li class="col-4">
                    <small>Balance</small>
                    <h6>0</h6>
                </li>
            </ul>
        </div>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#menu">Menu</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#Chat"><i class="icon-book-open"></i></a>
            </li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#setting"><i class="icon-settings"></i></a>
            </li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#question"><i
                        class="icon-question"></i></a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content padding-0">
            <div class="tab-pane active" id="menu">
                <nav id="left-sidebar-nav" class="sidebar-nav">
                    <ul id="main-menu" class="metismenu li_animation_delay">
                        <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i><span>Dashboard</span></a>
                        </li>
                        @if (auth()->user()->name == 'admin')
                            <li class="{{ request()->is('datamaster*') ? 'active' : '' }}">
                                <a href="#datamaster" class="has-arrow"><i class="fa fa-th-large"></i><span>Data
                                        Master</span></a>
                                <ul>
                                    <li class="{{ request()->is('datamaster/slider') ? 'active' : '' }}"><a
                                            href="{{ route('slider.index') }}">Banner</a></li>
                                    <li class="{{ request()->is('datamaster/price') ? 'active' : '' }}"><a
                                            href="{{ route('price.index') }}">Harga Perkota</a></li>
                                    {{-- <li class="{{ request()->is('datamaster/documentdriver') ? 'active' : '' }}"><a href="{{ route('documentdriver.index') }}">Driver Document</a></li> --}}
                                    <li class="{{ request()->is('datamaster/vehicletype') ? 'active' : '' }}"><a
                                            href="{{ route('vehicletype.index') }}">Tipe Kendaraan</a></li>
                                    <li class="{{ request()->is('datamaster/coupon') ? 'active' : '' }}"><a
                                            href="{{ route('coupon.index') }}">Kupon/Promo</a></li>


                                </ul>
                            </li>
                            <li class="{{ request()->is('driver*') ? 'active' : '' }}">
                                <a href="#Drivers" class="has-arrow"><i class="fa fa-users"></i><span>Driver</span></a>
                                <ul>
                                    <li class="{{ request()->is('driver') ? 'active' : '' }}"><a
                                            href="{{ route('driver.index') }}">Semua Driver</a></li>
                                    <li class="{{ request()->is('driver/approved') ? 'active' : '' }}"><a
                                            href="{{ route('driver.approved') }}">Driver Disetujui</a></li>
                                    <li class="{{ request()->is('driver/approval') ? 'active' : '' }}"><a
                                            href="{{ route('driver.approval') }}">Driver Baru</a></li>
                                    <li class="{{ request()->is('driver/suspend') ? 'active' : '' }}"><a
                                            href="{{ route('driver.suspend') }}">Driver Suspend</a></li>
                                    <li class="{{ request()->is('driver/deposit') ? 'active' : '' }}"><a
                                            href="{{ route('deposit-driver.index') }}">Semua Deposit</a></li>
                                    <li class="{{ request()->is('driver/setting-deposit*') ? 'active' : '' }}"><a
                                            href="{{ route('setting-deposit-driver.index') }}">Setting Deposit</a></li>
                                    <li
                                        class="{{ request()->is('driver/history-add-saldo-driver*') ? 'active' : '' }}">
                                        <a href="{{ route('history-add-saldo-driver.index') }}">Tambah Saldo</a>
                                    </li>

                                    <li
                                        class="{{ request()->is('driver/history-transaction-ppob-driver*') ? 'active' : '' }}">
                                        <a href="{{ route('history-transaction-ppob-driver.index') }}">Transaksi
                                            Pembelian</a>
                                    </li>


                                </ul>
                            </li>
                            <li class="{{ request()->is('merchant*') ? 'active' : '' }}">
                                <a href="#Merchants" class="has-arrow"><i
                                        class="fa fa-users"></i><span>Mitra/Pedagang</span></a>
                                <ul>
                                    <li class="{{ request()->is('merchant') ? 'active' : '' }}"><a
                                            href="{{ route('merchant.index') }}">Semua Pedagang</a></li>
                                    <li class="{{ request()->is('merchant/approved') ? 'active' : '' }}"><a
                                            href="{{ route('merchant.approved') }}">Pedagang Disetujui</a></li>
                                    <li class="{{ request()->is('merchant/suspend') ? 'active' : '' }}"><a
                                            href="{{ route('merchant.suspend') }}">Pedagang Ditolak</a></li>
                                    <li class="{{ request()->is('merchant/deposit') ? 'active' : '' }}"><a
                                            href="{{ route('deposit-merchant.index') }}">Semua Deposit</a></li>
                                    <li class="{{ request()->is('merchant/setting-deposit*') ? 'active' : '' }}"><a
                                            href="{{ route('setting-deposit-merchant.index') }}">Setting Deposit</a>
                                    </li>
                                    <li
                                        class="{{ request()->is('merchant/history-add-saldo-merchant*') ? 'active' : '' }}">
                                        <a href="{{ route('history-add-saldo-merchant.index') }}">Tambah Saldo</a>
                                    </li>
                                    <li
                                        class="{{ request()->is('merchant/history-transaction-ppob-merchant*') ? 'active' : '' }}">
                                        <a href="{{ route('history-transaction-ppob-merchant.index') }}">
                                            Transaksi Pembelian</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="{{ request()->is('products*') ? 'active' : '' }}">
                                <a href="#Merchants" class="has-arrow"><i
                                        class="fa fa-qrcode"></i><span>Produk</span></a>
                                <ul>
                                    <li class="{{ request()->is('product') ? 'active' : '' }}"><a
                                            href="{{ route('product.index') }}">Semua Produk</a></li>
                                    <li class="{{ request()->is('product/approved') ? 'active' : '' }}"><a
                                            href="{{ route('product.approved') }}">Produk Disetujui</a></li>
                                    <li class="{{ request()->is('product/suspend') ? 'active' : '' }}"><a
                                            href="{{ route('product.suspend') }}">Produk Ditolak</a></li>
                                    <li class="{{ request()->is('product/ppob') ? 'active' : '' }}"><a
                                            href="{{ route('productppob.index') }}">Produk PPOB</a></li>
                                    <li class="{{ request()->is('category-products') ? 'active' : '' }}"><a
                                            href="{{ route('category-products.index') }}">Kategori Produk</a></li>
                                    <li class="{{ request()->is('product/type-package') ? 'active' : '' }}"><a
                                            href="{{ route('type-packages.index') }}">Jenis Paket</a></li>
                                </ul>
                            </li>
                            <li class="{{ request()->is('customer*') ? 'active' : '' }}">
                                <a href="#customers" class="has-arrow"><i
                                        class="fa fa-users"></i><span>Customer</span></a>
                                <ul>
                                    <li class="{{ request()->is('customer') ? 'active' : '' }}"><a
                                            href="{{ route('customer.index') }}">Semua Customer</a></li>
                                    <li class="{{ request()->is('customer/deposit') ? 'active' : '' }}"><a
                                            href="{{ route('deposit-customer.index') }}">Semua Deposit</a></li>
                                    <li class="{{ request()->is('customer/setting-deposit') ? 'active' : '' }}">
                                        <a href="{{ route('setting-deposit-customer.index') }}">Setting Deposit</a>
                                    </li>
                                    <li
                                        class="{{ request()->is('customer/history-add-saldo-customer*') ? 'active' : '' }}">
                                        <a href="{{ route('history-add-saldo-customer.index') }}">Tambah Saldo</a>
                                    </li>
                                    <li
                                        class="{{ request()->is('customer/history-transaction-ppob-customer*') ? 'active' : '' }}">
                                        <a href="{{ route('history-transaction-ppob-customer.index') }}">Transaksi
                                            Pembelian</a>
                                    </li>

                                </ul>
                            </li>
                            {{-- <li class="{{ request()->is('deposit*') ? 'active' : '' }}">
                                <a href="{{ route('depositcustomer.index') }}"><i
                                        class="fa fa-random"></i><span>Transaksi Deposit</span></a>
                            </li> --}}
                            <li class="{{ request()->is('trip*') ? 'active' : '' }}">
                                <a href="{{ route('trip.index') }}"><i class="fa fa-map-marker"></i><span>Semua
                                        Perjalanan</span></a>
                            </li>
                            <li class="{{ request()->is('banks*') ? 'active' : '' }}">
                                <a href="{{ route('bank.index') }}"><i class="fa fa-map-marker"></i><span>Semua
                                        Bank</span></a>
                            </li>
                            <li class="{{ request()->is('admin*') ? 'active' : '' }}">
                                <a href="#account" class="has-arrow"><i class="fa fa-user-circle"
                                        aria-hidden="true"></i><span>Akun</span></a>
                                <ul>
                                    <li class="{{ request()->is('admin*') ? 'active' : '' }}"><a
                                            href="{{ route('admin.index') }}">User Gpar</a>
                                    </li>
                                    <li class="{{ request()->is('admin*') ? 'active' : '' }}"><a
                                            href="{{ route('koordinator.index') }}">Akun Koordinator</a>
                                    </li>
                                    {{-- <li><a href="h-menu.html">Analytical H-Menu</a></li>
                                    <li><a href="index9.html">IoT Dashboard</a></li>
                                    <li><a href="index2.html">Demographic</a></li>
                                    <li><a href="index6.html">Project Board</a></li>
                                    <li><a href="index7.html">Crypto Dashboard</a></li>
                                    <li><a href="index8.html">eCommerce</a></li> --}}
                                </ul>
                            </li>
                            <li class="{{ request()->is('settings*') ? 'active' : '' }}">
                                <a href="{{ route('settings.index') }}"><i
                                        class="fa fa-cog"></i><span>Setting</span></a>
                            </li>
                        @elseif(auth()->user()->name == 'manager')
                            <li class="{{ request()->is('datamaster*') ? 'active' : '' }}">
                                <a href="#datamaster" class="has-arrow"><i class="fa fa-th-large"></i><span>Data
                                        Master</span></a>
                                <ul>
                                    <li class="{{ request()->is('datamaster/slider') ? 'active' : '' }}"><a
                                            href="{{ route('slider.index') }}">Slider</a></li>
                                    <li class="{{ request()->is('datamaster/price') ? 'active' : '' }}"><a
                                            href="{{ route('price.index') }}">Price Per City</a></li>
                                    <li class="{{ request()->is('datamaster/documentdriver') ? 'active' : '' }}"><a
                                            href="{{ route('documentdriver.index') }}">Driver Document</a></li>
                                    <li class="{{ request()->is('datamaster/vehicletype') ? 'active' : '' }}"><a
                                            href="{{ route('vehicletype.index') }}">Vehicle Type</a></li>
                                    <li class="{{ request()->is('datamaster/coupon') ? 'active' : '' }}"><a
                                            href="{{ route('coupon.index') }}">Coupons/Promo</a></li>
                                    {{-- <li class="{{ request()->is('driver/approval') ? 'active' : '' }}"><a href="{{ route('driver.approval') }}">Approval Pending</a></li> --}}

                                </ul>
                            </li>
                            <li class="{{ request()->is('driver*') ? 'active' : '' }}">
                                <a href="#Drivers" class="has-arrow"><i
                                        class="fa fa-users"></i><span>Driver</span></a>
                                <ul>
                                    <li class="{{ request()->is('driver') ? 'active' : '' }}"><a
                                            href="{{ route('driver.index') }}">All Driver</a></li>
                                    <li class="{{ request()->is('driver/approved') ? 'active' : '' }}"><a
                                            href="{{ route('driver.approved') }}">Approved Driver</a></li>
                                    <li class="{{ request()->is('driver/approval') ? 'active' : '' }}"><a
                                            href="{{ route('driver.approval') }}">Approval Pending</a></li>

                                </ul>
                            </li>
                            <li class="{{ request()->is('customer*') ? 'active' : '' }}">
                                <a href="{{ route('customer.index') }}"><i
                                        class="fa fa-users"></i><span>Customer</span></a>
                            </li>
                            <li class="{{ request()->is('trip*') ? 'active' : '' }}">
                                <a href="{{ route('trip.index') }}"><i class="fa fa-map-marker"></i><span>All
                                        Trips</span></a>
                            </li>
                        @elseif(auth()->user()->name == 'customer service')
                            <li class="{{ request()->is('driver*') ? 'active' : '' }}">
                                <a href="#Drivers" class="has-arrow"><i
                                        class="fa fa-users"></i><span>Driver</span></a>
                                <ul>
                                    <li class="{{ request()->is('driver') ? 'active' : '' }}"><a
                                            href="{{ route('driver.index') }}">All Driver</a></li>
                                </ul>
                            </li>
                            <li class="{{ request()->is('customer*') ? 'active' : '' }}">
                                <a href="{{ route('customer.index') }}"><i
                                        class="fa fa-users"></i><span>Customer</span></a>
                            </li>
                            <li class="{{ request()->is('trip*') ? 'active' : '' }}">
                                <a href="{{ route('trip.index') }}"><i class="fa fa-map-marker"></i><span>All
                                        Trips</span></a>
                            </li>
                        @elseif (auth()->user()->getRoleNames()->first() == 'Korwil')
                            <li class="{{ request()->is('driver*') ? 'active' : '' }}">
                                <a href="#Drivers" class="has-arrow"><i
                                        class="fa fa-users"></i><span>Driver</span></a>
                                <ul>
                                    <li class="{{ request()->is('driver') ? 'active' : '' }}"><a
                                            href="{{ route('korwil.drivers.index') }}">Semua Driver</a></li>
                                </ul>
                            </li>
                            <li class="{{ request()->is('customer*') ? 'active' : '' }}">
                                <a href="#Customers" class="has-arrow"><i
                                        class="fa fa-users"></i><span>Customer</span></a>
                                <ul>
                                    <li class="{{ request()->is('customer') ? 'active' : '' }}"><a
                                            href="{{ route('korwil.customers.index') }}">Semua Customer</a></li>
                                </ul>
                            </li>
                            <li class="{{ request()->is('merchant*') ? 'active' : '' }}">
                                <a href="#Merchants" class="has-arrow"><i
                                        class="fa fa-users"></i><span>Merchant</span></a>
                                <ul>
                                    <li class="{{ request()->is('merchant') ? 'active' : '' }}"><a
                                            href="{{ route('korwil.merchants.index') }}">Semua Merchant</a></li>
                                </ul>
                            </li>
                        @endif

                    </ul>
                </nav>
            </div>
            <div class="tab-pane" id="Chat">
                <form>
                    <div class="input-group m-b-20">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="icon-magnifier"></i></span>
                        </div>
                        <input type="text" class="form-control" placeholder="Search...">
                    </div>
                </form>
                <ul class="right_chat list-unstyled li_animation_delay">
                    <li>
                        <a href="javascript:void(0);" class="media">
                            <img class="media-object" src="/assets/images/xs/avatar1.jpg" alt="">
                            <div class="media-body">
                                <span class="name d-flex justify-content-between">Chris Fox <i
                                        class="fa fa-heart-o font-12"></i></span>
                                <span class="message">chrisfox@gmail.com</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="media">
                            <img class="media-object" src="/assets/images/xs/avatar2.jpg" alt="">
                            <div class="media-body">
                                <span class="name d-flex justify-content-between">Joge Lucky <i
                                        class="fa fa-heart-o font-12"></i></span>
                                <span class="message">Jogelucky@gmail.com</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="media">
                            <img class="media-object" src="/assets/images/xs/avatar3.jpg" alt="">
                            <div class="media-body">
                                <span class="name d-flex justify-content-between">Isabella <i
                                        class="fa fa-heart-o font-12"></i></span>
                                <span class="message">Isabella@gmail.com</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="media">
                            <img class="media-object" src="/assets/images/xs/avatar4.jpg" alt="">
                            <div class="media-body">
                                <span class="name d-flex justify-content-between">Folisise Chosielie <i
                                        class="fa fa-heart font-12"></i></span>
                                <span class="message">FolisiseChosielie@gmail.com</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="media">
                            <img class="media-object" src="/assets/images/xs/avatar5.jpg" alt="">
                            <div class="media-body">
                                <span class="name d-flex justify-content-between">Alexander <i
                                        class="fa fa-heart-o font-12"></i></span>
                                <span class="message">Alexander@gmail.com</span>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-pane" id="setting">
                <h6>Choose Skin</h6>
                <ul class="choose-skin list-unstyled">
                    <li data-theme="purple">
                        <div class="purple"></div>
                    </li>
                    <li data-theme="blue">
                        <div class="blue"></div>
                    </li>
                    <li data-theme="cyan" class="active">
                        <div class="cyan"></div>
                    </li>
                    <li data-theme="green">
                        <div class="green"></div>
                    </li>
                    <li data-theme="orange">
                        <div class="orange"></div>
                    </li>
                    <li data-theme="blush">
                        <div class="blush"></div>
                    </li>
                    <li data-theme="red">
                        <div class="red"></div>
                    </li>
                </ul>

                <ul class="list-unstyled font_setting mt-3">
                    <li>
                        <label class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" name="font" value="font-nunito"
                                checked="">
                            <span class="custom-control-label">Nunito Google Font</span>
                        </label>
                    </li>
                    <li>
                        <label class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" name="font" value="font-ubuntu">
                            <span class="custom-control-label">Ubuntu Font</span>
                        </label>
                    </li>
                    <li>
                        <label class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" name="font" value="font-raleway">
                            <span class="custom-control-label">Raleway Google Font</span>
                        </label>
                    </li>
                    <li>
                        <label class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" name="font" value="font-IBMplex">
                            <span class="custom-control-label">IBM Plex Google Font</span>
                        </label>
                    </li>
                </ul>

                <ul class="list-unstyled mt-3">
                    <li class="d-flex align-items-center mb-2">
                        <label class="toggle-switch theme-switch">
                            <input type="checkbox">
                            <span class="toggle-switch-slider"></span>
                        </label>
                        <span class="ml-3">Enable Dark Mode!</span>
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <label class="toggle-switch theme-rtl">
                            <input type="checkbox">
                            <span class="toggle-switch-slider"></span>
                        </label>
                        <span class="ml-3">Enable RTL Mode!</span>
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <label class="toggle-switch theme-high-contrast">
                            <input type="checkbox">
                            <span class="toggle-switch-slider"></span>
                        </label>
                        <span class="ml-3">Enable High Contrast Mode!</span>
                    </li>
                </ul>

                <hr>
                <h6>General Settings</h6>
                <ul class="setting-list list-unstyled">
                    <li>
                        <label class="fancy-checkbox">
                            <input type="checkbox" name="checkbox" checked>
                            <span>Allowed Notifications</span>
                        </label>
                    </li>
                    <li>
                        <label class="fancy-checkbox">
                            <input type="checkbox" name="checkbox">
                            <span>Offline</span>
                        </label>
                    </li>
                    <li>
                        <label class="fancy-checkbox">
                            <input type="checkbox" name="checkbox">
                            <span>Location Permission</span>
                        </label>
                    </li>
                </ul>


            </div>
            <div class="tab-pane" id="question">
                <form>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="icon-magnifier"></i></span>
                        </div>
                        <input type="text" class="form-control" placeholder="Search...">
                    </div>
                </form>
                <ul class="list-unstyled question">
                    <li class="menu-heading">HOW-TO</li>
                    <li><a href="javascript:void(0);">How to Create Campaign</a></li>
                    <li><a href="javascript:void(0);">Boost Your Sales</a></li>
                    <li><a href="javascript:void(0);">Website Analytics</a></li>
                    <li class="menu-heading">ACCOUNT</li>
                    <li><a href="javascript:void(0);">Cearet New Account</a></li>
                    <li><a href="javascript:void(0);">Change Password?</a></li>
                    <li><a href="javascript:void(0);">Privacy &amp; Policy</a></li>
                    <li class="menu-heading">BILLING</li>
                    <li><a href="javascript:void(0);">Payment info</a></li>
                    <li><a href="javascript:void(0);">Auto-Renewal</a></li>
                    <li class="menu-button mt-3">
                        <a href="../docs/index.html" class="btn btn-primary btn-block">Documentation</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
